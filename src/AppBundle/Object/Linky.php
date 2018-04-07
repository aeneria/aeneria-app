<?php

namespace AppBundle\Object;


/**
 * Linky API 
 * 
 * @seehttps://github.com/KiboOst/php-LinkyAPI *
 */
class Linky{
        
    public function getData_perhour($date)
    {
        //start from date - 2days to date + 1 day...
        $endDate = DateTime::createFromFormat('d/m/Y', $date)->add(new DateInterval('P1D'));
        $endDate = $endDate->format('d/m/Y');
        $startDate = DateTime::createFromFormat('d/m/Y', $date);
        $startDate->sub(new DateInterval('P2D'));
        $startDate = $startDate->format('d/m/Y');
        
        $resource_id = 'urlCdcHeure';
        $result = $this->getData($resource_id, $startDate, $endDate);
        
        //format this correctly:
        $returnData = array();
        $startHour = new DateTime('23:30');
        
        $data = $result['graphe']['data'];
        $end = count($data);
        for($i=$end-1; $i>=$end-48; $i--)
        {
            $valeur = $data[$i]['valeur'].'kW';
            
            $thisHour = clone $startHour;
            $thisHour = $thisHour->format('H:i');
            
            $returnData[$thisHour] = $valeur;
            $startHour->modify('-30 min');
        }
        
        $returnData = array_reverse($returnData);
        $this->_data['hours'][$date] = $returnData;
        return array($date=>$returnData);
    }
    
    public function getData_perday($startDate, $endDate)
    {
        //max 31days:
        $date1 = DateTime::createFromFormat('d/m/Y', $startDate);
        $date2 = DateTime::createFromFormat('d/m/Y', $endDate);
        $nbr_ts = $date2->getTimestamp() - $date1->getTimestamp();
        $nbr_days = $nbr_ts/86400;
        if ($nbr_days > 31) return array('etat'=>null, 'error' => 'Max number of days can not exceed 31 days');
        
        $resource_id = 'urlCdcJour';
        $result = $this->getData($resource_id, $startDate, $endDate);
        
        //format this correctly:
        $returnData = array();
        
        $data = $result['graphe']['data'];
        foreach ($data as $day)
        {
            $valeur = $day['valeur'];
            if ($valeur == -2) $valeur = null;
            else $valeur .= 'kWh';
            
            $date = $date1;
            $date = $date1->format("d/m/Y");
            $returnData[$date] = $valeur;
            $date1->modify('+1 day');
        }
        
        $this->_data['days'] = $returnData;
        return $returnData;
    }
    
    public function getData_permonth($startDate, $endDate)
    {
        $resource_id = 'urlCdcMois';
        $result = $this->getData($resource_id, $startDate, $endDate);
        
        //format this correctly:
        $fromMonth = DateTime::createFromFormat('d/m/Y', $startDate);
        $returnData = array();
        
        $data = $result['graphe']['data'];
        foreach ($data as $month)
        {
            $valeur = $month['valeur'];
            if ($valeur == -2) $valeur = null;
            else $valeur .= 'kW';
            
            $thisMonth = $fromMonth;
            $thisMonth = $thisMonth->format('M Y');
            
            $returnData[$thisMonth] = $valeur;
            $fromMonth->modify('+1 month');
        }
        
        $this->_data['months'] = $returnData;
        return $returnData;
    }
    
    public function getData_peryear()
    {
        $resource_id = 'urlCdcAn';
        $result = $this->getData($resource_id, null, null);
        
        //format this correctly:
        $fromYear = new DateTime();
        $returnData = array();
        
        $data = $result['graphe']['data'];
        $c = count($data)-1;
        $fromYear->modify('- '.$c.' year');
        foreach ($data as $year)
        {
            $valeur = $year['valeur'];
            if ($valeur == -2) $valeur = null;
            else $valeur .= 'kW';
            
            $thisYear = $fromYear;
            $thisYear = $thisYear->format('Y');
            
            $returnData[$thisYear] = $valeur;
            $fromYear->modify('+1 year');
        }
        
        $this->_data['years'] = $returnData;
        return $returnData;
    }
    
    
    //INTERNAL FUNCTIONS==================================================
    public function getAll()
    {
        //____Initialize datas:
        $timezone = 'Europe/Paris';
        $today = new DateTime('NOW', new DateTimeZone($timezone));
        $today->sub(new DateInterval('P1D')); //Enedis last data are yesterday
        
        //____Get per hour for yesterday:
        $yesterday = $today->format('d/m/Y');
        $this->getData_perhour($yesterday);
        
        //____Get per day
        $var = clone $today;
        $fromMonth = $var->sub(new DateInterval('P30D'));
        $fromMonth = $fromMonth->format('d/m/Y');
        $this->getData_perday($fromMonth, $yesterday);
        
        //____Get per month
        $var = clone $today;
        $fromYear = $var->sub(new DateInterval('P1Y'));
        $fromYear = $fromYear->format('01/'.'m/Y');
        $this->getData_permonth($fromYear, $yesterday);
        
        //____Get per year
        $this->getData_peryear();
    }
    
    
    
    //______________________calling functions
    protected function _request($method, $url, $postdata=null) //standard function handling all get/post request with curl | return string
    {
        if (!isset($this->_curlHdl))
        {
            $this->_curlHdl = curl_init();
            curl_setopt($this->_curlHdl, CURLOPT_COOKIEJAR, $this->_cookFile);
            curl_setopt($this->_curlHdl, CURLOPT_COOKIEFILE, $this->_cookFile);
            
            curl_setopt($this->_curlHdl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->_curlHdl, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_setopt($this->_curlHdl, CURLOPT_HEADER, true);
            curl_setopt($this->_curlHdl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->_curlHdl, CURLOPT_FOLLOWLOCATION, true);
            
            curl_setopt($this->_curlHdl, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0');
        }
        
        $url = filter_var($url, FILTER_SANITIZE_URL);
        //echo 'url: ', $url, "<br>";
        curl_setopt($this->_curlHdl, CURLOPT_URL, $url);
        
        if ($method == 'POST')
        {
            curl_setopt($this->_curlHdl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->_curlHdl, CURLOPT_POST, true);
        }
        else
        {
            curl_setopt($this->_curlHdl, CURLOPT_POST, false);
        }
        
        if ( isset($postdata) )
        {
            curl_setopt($this->_curlHdl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->_curlHdl, CURLOPT_POSTFIELDS, $postdata);
        }
        
        $response = curl_exec($this->_curlHdl);
        
        //$info   = curl_getinfo($this->_curlHdl);
        //echo "<pre>cURL info".json_encode($info, JSON_PRETTY_PRINT)."</pre><br>";
        
        $this->error = null;
        if ($response === false) $this->error = curl_error($this->_curlHdl);
        
        if ($this->_isAuth == true)
        {
            $header_size = curl_getinfo($this->_curlHdl, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $response = substr($response, $header_size);
        }
        return $response;
    }
    
    
    //______________________Internal cooking
    protected function getData($resource_id, $startDate, $endDate)
    {
        $p_p_id = 'lincspartdisplaycdc_WAR_lincspartcdcportlet';
        
        $url = $this->_APIBaseUrl.$this->_APIDataUrl;
        $url .= '?p_p_id='.$p_p_id;
        $url .= '&p_p_lifecycle=2';
        $url .= '&p_p_mode=view';
        $url .= '&p_p_resource_id='.$resource_id;
        $url .= '&p_p_cacheability=cacheLevelPage';
        $url .= '&p_p_col_id=column-1';
        $url .= '&p_p_col_count=2';
        
        $postdata = null;
        if ($startDate)
        {
            $postdata = http_build_query(
                    array(
                        '_'.$p_p_id.'_dateDebut' => $startDate,
                        '_'.$p_p_id.'_dateFin' => $endDate
                    )
                    );
        }
        
        $response = $this->_request('GET', $url, $postdata);
        $jsonArray = json_decode($response, true);
        return $jsonArray;
    }
    
    public $error = null;
    public $_isAuth = false;
    
    public $_data = array();
    
    //authentication:
    protected $_login;
    protected $_password;
    
    protected $_cookFile = '';
    
    protected $_loginBaseUrl = 'https://espace-client-connexion.enedis.fr';
    protected $_APIBaseUrl = 'https://espace-client-particuliers.enedis.fr/group/espace-particuliers';
    protected $_APILoginUrl = '/auth/UI/Login';
    protected $_APIHomeUrl = '/home';
    protected $_APIDataUrl = '/suivi-de-consommation';
    
    protected $_curlHdl = null;
    
    protected function auth()
    {
        $postdata = http_build_query(
                array(
                    'IDToken1' => $this->_login,
                    'IDToken2' => $this->_password,
                    'SunQueryParamsString' => base64_encode('realm=particuliers'),
                    'encoded' => 'true',
                    'gx_charset' => 'UTF-8'
                )
                );
        
        $url = $this->_loginBaseUrl.$this->_APILoginUrl;
        $response = $this->_request('POST', $url, $postdata);
        
        //connected ?
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = array();
        foreach($matches[1] as $item)
        {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        if (!array_key_exists('iPlanetDirectoryPro', $cookies))
        {
            $this->error = 'Sorry, could not connect. Check your credentials.';
            return false;
        }
        
        $this->_isAuth = true;
        
        $url = 'https://espace-client-particuliers.enedis.fr/group/espace-particuliers/accueil';
        $response = $this->_request('GET', $url);
        
        return true;
    }
    
    function __construct($login, $password, $getAll=false)
    {
        $this->_login = $login;
        $this->_password = $password;
        
        if ($this->auth() == false)
        {
            return $this->error;
        }
        
        if ($getAll)
        {
            $this->getAll();
        }
    }
    
    //Linky end
}

