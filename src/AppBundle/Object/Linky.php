<?php

namespace AppBundle\Object;


use AppBundle\Entity\Feed;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\DataValue;

/**
 * Linky API
 *
 * @see https://github.com/KiboOst/php-LinkyAPI
 * @todo simply curl request by guzzle ones
 */
class Linky {

    /**
     * Differents usefull URIs.
     */
    const LOGIN_BASE_URL = 'https://espace-client-connexion.enedis.fr';
    const API_BASE_URL = 'https://espace-client-particuliers.enedis.fr/group/espace-particuliers';
    const API_LOGIN_URL = '/auth/UI/Login';
    const API_HOME_URL = '/home';
    const API_DATA_URL = '/suivi-de-consommation';


    /**
     * Feed corresponding to the Linky Object.
     * @var Feed
     */
    private $feed;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Error.
     * @var mixed
     */
    private $error = NULL;

    /**
     * Authentifications variables.
     * @var string
     */
    private $login, $password;

    /**
     * Is connected
     * @var boolean
     */
    private $isAuth = FALSE;

    /**
     * Authentification cookie.
     * @var string
     */
    private $cookFile = '';

    private $curlHdl = NULL;

    /**
     * Constructor.
     *
     * @param Feed $feed
     * @param EntityManager $entityManager
     * @return boolean error
     */
    public function __construct($feed, $entityManager)
    {
        $this->feed = $feed;
        $feedParam = $feed->getParam();
        $this->login = $feedParam['LOGIN'];
        $this->password = $feedParam['PASSWORD'];

        $this->entityManager = $entityManager;

        if (!$this->auth()) {
            return $this->error;
        }
    }

    /**
     * Fetch ENEDIS data for yesterday and persist its in database.
     *
     * @param \DateTime $date
     */
    public function fetchData(\DateTime $date) {
        $this->getAll($date);
        $this->persistData($date);
    }

    /**
     * Persist data in database.
     *
     * @param \DateTime $date
     */
    private function persistData(\DateTime $date) {
        $date = new \DateTime($date->format("Y-m-d 00:00:00"));

        // Get feedData.
        /** @var \AppBundle\Entity\FeedData $feedData */
        $feedData = $this->entityManager->getRepository('AppBundle:FeedData')->findOneByFeed($this->feed);

        // Persist hours data.
        foreach (end($this->data['hours']) as $hour => $value) {
            if ($value) {
                $feedData->updateOrCreateValue(
                    new \DateTime($date->format("Y-m-d") . $hour . ':00'),
                    DataValue::FREQUENCY['HOUR'],
                    $value,
                    $this->entityManager
                );
            }
        }

        // Persist day data.
        if (end($this->data['days'])) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['DAY'],
                end($this->data['days']),
                $this->entityManager
            );
        }

        // Persist month data.
        if (end($this->data['months'])) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['MONTH'],
                end($this->data['months']),
                $this->entityManager
            );
        }

        // Persist day data.
        if (end($this->data['years'])) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['YEAR'],
                end($this->data['years']),
                $this->entityManager
            );
        }

        // Flush all persisted DataValue.
        $this->entityManager->flush();
    }

    public function getDataPerHour($date)
    {
        // Start from date - 2days to date + 1 day...
        $endDate = \DateTime::createFromFormat('d/m/Y', $date)->add(new \DateInterval('P1D'));
        $endDate = $endDate->format('d/m/Y');
        $startDate = \DateTime::createFromFormat('d/m/Y', $date);
        $startDate->sub(new \DateInterval('P2D'));
        $startDate = $startDate->format('d/m/Y');

        $resource_id = 'urlCdcHeure';
        $result = $this->getData($resource_id, $startDate, $endDate);

        // Format this correctly:
        $returnData = [];
        $startHour = new \DateTime('23:00');

        if (!empty($result['graphe']['data'])) {
            $data = $result['graphe']['data'];
            $end = count($data);
            for($i=$end-1; $i>=$end-48; $i-=2) {

                if ($data[$i]['valeur'] == -2 || $data[$i - 1]['valeur'] == -2) {
                    $valeur = NULL;
                }
                else {
                    $valeur = $data[$i]['valeur'] + $data[$i - 1]['valeur'];
                }

                $thisHour = clone $startHour;
                $thisHour = $thisHour->format('H:i');

                $returnData[$thisHour] = $valeur;
                $startHour->modify('-60 min');
            }
        }

        $returnData = array_reverse($returnData);
        $this->data['hours'][$date] = $returnData;
        return [$date=>$returnData];
    }

    public function getDataPerDay($startDate, $endDate)
    {
        // Max 31 days:
        $date1 = \DateTime::createFromFormat('d/m/Y', $startDate);
        $date2 = \DateTime::createFromFormat('d/m/Y', $endDate);
        $nbr_ts = $date2->getTimestamp() - $date1->getTimestamp();
        $nbr_days = $nbr_ts/86400;
        if ($nbr_days > 31) {
            return [
                'etat'=>NULL, 'error' => 'Max number of days can not exceed 31 days'
            ];
        }

        $resource_id = 'urlCdcJour';
        $result = $this->getData($resource_id, $startDate, $endDate);

        // Format this correctly:
        $returnData = [];

        $data = $result['graphe']['data'];
        foreach ((array)$data as $day) {
            $valeur = $day['valeur'];
            if ($valeur == -2) {
              $valeur = NULL;
            }

            $date = $date1;
            $date = $date1->format("d/m/Y");
            $returnData[$date] = $valeur;
            $date1->modify('+1 day');
        }

        $this->data['days'] = $returnData;
        return $returnData;
    }

    public function getDataPerMonth($startDate, $endDate)
    {
        $resource_id = 'urlCdcMois';
        $result = $this->getData($resource_id, $startDate, $endDate);

        // Format this correctly:
        $fromMonth = \DateTime::createFromFormat('d/m/Y', $startDate);
        $returnData = [];

        $data = $result['graphe']['data'];
        foreach ((array)$data as $month) {
            $valeur = $month['valeur'];
            if ($valeur == -2) {
              $valeur = NULL;
            }

            $thisMonth = $fromMonth;
            $thisMonth = $thisMonth->format('M Y');

            $returnData[$thisMonth] = $valeur;
            $fromMonth->modify('+1 month');
        }

        $this->data['months'] = $returnData;
        return $returnData;
    }

    public function getDataPerYear()
    {
        $resource_id = 'urlCdcAn';
        $result = $this->getData($resource_id, NULL, NULL);

        // Format this correctly:
        $fromYear = new \DateTime();
        $returnData = [];

        $data = $result['graphe']['data'];
        if ($data) {
            $c = count($data)-1;
            $fromYear->modify('- '.$c.' year');
            foreach ((array)$data as $year)
            {
                $valeur = $year['valeur'];
                if ($valeur == -2) {
                  $valeur = NULL;
                }

                $thisYear = $fromYear;
                $thisYear = $thisYear->format('Y');

                $returnData[$thisYear] = $valeur;
                $fromYear->modify('+1 year');
            }

        }

        $this->data['years'] = $returnData;
        return $returnData;
    }

    /**
     * Get data for all frequencies for $date.
     *
     * @param \DateTime $date
     * @return array of data.
     */
    public function getAll(\DateTime $date)
    {
        // Get per hour for date:
        $formattedDate = $date->format('d/m/Y');
        $this->getDataPerHour($formattedDate);

        // Get per day:
        $var = clone $date;
        $fromMonth = $var->sub(new \DateInterval('P30D'));
        $fromMonth = $fromMonth->format('d/m/Y');
        $this->getDataPerDay($fromMonth, $formattedDate);

        // Get per month:
        $var = clone $date;
        $fromYear = $var->sub(new \DateInterval('P1Y'));
        $fromYear = $fromYear->format('01/'.'m/Y');
        $this->getDataPerMonth($fromYear, $formattedDate);

        // Get per year:
        $this->getDataPerYear();

        return $this->data;
    }

    /**
     * Standard function handling all get/post request with curl | return string
     * @param string $method
     * @param string $url
     * @param array $postdata
     * @return string
     */
    private function request($method, $url, $postdata=NULL) //
    {
        if (!isset($this->curlHdl))
        {
            $this->curlHdl = curl_init();
            curl_setopt($this->curlHdl, CURLOPT_COOKIEJAR, $this->cookFile);
            curl_setopt($this->curlHdl, CURLOPT_COOKIEFILE, $this->cookFile);

            curl_setopt($this->curlHdl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($this->curlHdl, CURLOPT_SSL_VERIFYPEER, FALSE);

            curl_setopt($this->curlHdl, CURLOPT_HEADER, TRUE);
            curl_setopt($this->curlHdl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->curlHdl, CURLOPT_FOLLOWLOCATION, TRUE);

            curl_setopt($this->curlHdl, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0');
        }

        $url = filter_var($url, FILTER_SANITIZE_URL);
        curl_setopt($this->curlHdl, CURLOPT_URL, $url);

        if ($method == 'POST') {
            curl_setopt($this->curlHdl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->curlHdl, CURLOPT_POST, TRUE);
        }
        else {
            curl_setopt($this->curlHdl, CURLOPT_POST, FALSE);
        }

        if ( isset($postdata) ) {
            curl_setopt($this->curlHdl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curlHdl, CURLOPT_POSTFIELDS, $postdata);
        }

        $response = curl_exec($this->curlHdl);

        $this->error = NULL;
        if ($response === FALSE) {
          $this->error = curl_error($this->curlHdl);
        }

        if ($this->isAuth) {
            $header_size = curl_getinfo($this->curlHdl, CURLINFO_HEADER_SIZE);
            $response = substr($response, $header_size);
        }

        return $response;
    }

    private function getData($resource_id, $startDate, $endDate)
    {
        $p_p_id = 'lincspartdisplaycdc_WAR_lincspartcdcportlet';

        $url = self::API_BASE_URL . self::API_DATA_URL;
        $url .= '?p_p_id=' . $p_p_id;
        $url .= '&p_p_lifecycle=2';
        $url .= '&p_p_mode=view';
        $url .= '&p_p_resource_id=' . $resource_id;
        $url .= '&p_p_cacheability=cacheLevelPage';
        $url .= '&p_p_col_id=column-1';
        $url .= '&p_p_col_count=2';

        $postdata = NULL;
        if ($startDate) {
            $postdata = http_build_query(
                [
                    '_' . $p_p_id . '_dateDebut' => $startDate,
                    '_' . $p_p_id . '_dateFin' => $endDate
                ]
            );
        }

        $response = $this->request('GET', $url, $postdata);

        return json_decode($response, TRUE);
    }

    private function auth()
    {
        $postdata = http_build_query(
            [
                'IDToken1' => $this->login,
                'IDToken2' => $this->password,
                'SunQueryParamsString' => base64_encode('realm=particuliers'),
                'encoded' => 'true',
                'gx_charset' => 'UTF-8'
            ]
        );

        $url = self::LOGIN_BASE_URL . self::API_LOGIN_URL;
        $response = $this->request('POST', $url, $postdata);

        // Connected ?
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
            return FALSE;
        }

        $url = 'https://espace-client-particuliers.enedis.fr/group/espace-particuliers/accueil';
        $response = $this->request('GET', $url);

        $this->isAuth = TRUE;

        return $this->isAuth;
    }

}

