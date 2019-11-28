<?php

namespace App\FeedDataProvider;


use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use Doctrine\ORM\EntityManager;

/**
 * Linky Data Provider
 *
 * @see https://github.com/KiboOst/php-LinkyAPI
 * @todo simply curl request by guzzle ones
 * @todo week data
 */
class LinkyDataProvider extends AbstractDataProvider
{
    /**
     * Differents usefull URIs.
     */
    const LOGIN_BASE_URL = 'https://espace-client-connexion.enedis.fr';
    const API_BASE_URL = 'https://espace-client-particuliers.enedis.fr/group/espace-particuliers';
    const API_LOGIN_URL = '/auth/UI/Login';
    const API_HOME_URL = '/home';
    const API_DATA_URL = '/suivi-de-consommation';

    /**
     * Frequencies for Linky FeedData.
     * @deprecated use getFrequencies() instead.
     * @var array
     */
    const FREQUENCY = DataValue::FREQUENCY;

    /**
     * Feed corresponding to the Linky Object.
     * @var Feed
     */
    private $feed;

    /**
     * Error.
     * @var mixed
     */
    private $error = null;

    /**
     * Authentification cookie.
     * @var string
     */
    private $cookFile = '';

    private $curlHdl = null;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * {@inheritDoc}
     * @see \App\FeedObject\FeedObject::getFrequencies()
     */
    public static function getFrequencies()
    {
        return DataValue::FREQUENCY;
    }

    /**
     * Fetch ENEDIS data for $date and persist its in database.
     *
     * @param \DateTime $date
     */
    public function fetchData(Feed $feed, \DateTime $date)
    {
        $feedParam = $feed->getParam();
        $this->login = $feedParam['LOGIN'];
        $this->password = $feedParam['PASSWORD'];

        $this->auth($feedParam['LOGIN'], $feedParam['PASSWORD']);

        if ( $this->auth($feedParam['LOGIN'], $feedParam['PASSWORD'])) {
            $this->getAll($date);
            $this->persistData($date);
        }
    }

    /**
     * Persist data in database.
     *
     * @param \DateTime $date
     */
    private function persistData(\DateTime $date)
    {
        $date = new \DateTime($date->format("Y-m-d 00:00:00"));

        // Get feedData.
        /** @var \App\Entity\FeedData $feedData */
        $feedData = $this->entityManager->getRepository('App:FeedData')->findOneByFeed($this->feed);

        // Persist hours data.
        foreach (\end($this->data['hours']) as $hour => $value) {
            if ($value && (int)$value !== -1) {
                $feedData->updateOrCreateValue(
                    new \DateTime($date->format("Y-m-d") . $hour . ':00'),
                    DataValue::FREQUENCY['HOUR'],
                    $value,
                    $this->entityManager
                );
            }
        }

        // Persist day data.
        $value = \end($this->data['days']);
        if ($value && (int)$value !== -1) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['DAY'],
                $value,
                $this->entityManager
            );
        }

        // Persist month data.
        $value = \end($this->data['months']);
        if ($value && (int)$value !== -1) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['MONTH'],
                $value,
                $this->entityManager
            );
        }

        // Persist year data.
        $value = \end($this->data['years']);
        if ($value && (int)$value !== -1) {
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['YEAR'],
                $value,
                $this->entityManager
            );
        }

        // Flush all persisted DataValue.
        $this->entityManager->flush();

        // Persist week data.
        $this->persistWeekValue($date, $feedData);
        $this->persistYearValue($date, $feedData);
        $this->entityManager->flush();
    }

    /**
     * Create or refresh week agregate data for the date.
     * Persist it in EntityManager
     *
     * @param \DateTime $date
     */
    private function persistWeekValue(\DateTime $date, FeedData $feedData)
    {
        $firstDayOfWeek = clone $date;
        $w = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
        $firstDayOfWeek->sub(new \DateInterval('P' . $w . 'D'));

        $lastDayOfWeek = clone $firstDayOfWeek;
        $lastDayOfWeek->add(new \DateInterval('P6D'));

        $agregateData = $this
            ->entityManager
            ->getRepository('App:DataValue')
            ->getSumValue(
                $firstDayOfWeek,
                $lastDayOfWeek,
                $feedData,
                DataValue::FREQUENCY['DAY']
            );

        if (isset($agregateData[0]['value'])) {
            $feedData->updateOrCreateValue(
                $firstDayOfWeek,
                DataValue::FREQUENCY['WEEK'],
                \round($agregateData[0]['value'], 1),
                $this->entityManager
            );
        }
    }

    /**
     * Create or refresh year agregate data for the date.
     * Persist it in EntityManager
     *
     * @param \DateTime $date
     */
    private function persistYearValue(\DateTime $date, FeedData $feedData)
    {
        $firstDayOfYear = new \DateTime($date->format("Y-1-1 00:00:00"));;

        $lastDayOfYear = clone $firstDayOfYear;
        $lastDayOfYear->add(new \DateInterval('P1Y'));

        $agregateData = $this
            ->entityManager
            ->getRepository('App:DataValue')
            ->getSumValue(
                $firstDayOfYear,
                $lastDayOfYear,
                $feedData,
                DataValue::FREQUENCY['MONTH']
            );

        if (isset($agregateData[0]['value'])) {
            $feedData->updateOrCreateValue(
                $firstDayOfYear,
                DataValue::FREQUENCY['YEAR'],
                \round($agregateData[0]['value'], 1),
                $this->entityManager
            );
        }
    }

    private function getDataPerHour(\Datetime $date)
    {
        // Start from date - 2days to date + 1 day...
        $endDate = clone $date;
        $endDate->add(new \DateInterval('P1D'));
        $endDate = $endDate->format('d/m/Y');
        $startDate = clone $date;
        $startDate->sub(new \DateInterval('P2D'));
        $startDate = $startDate->format('d/m/Y');

        $resource_id = 'urlCdcHeure';
        $result = $this->getData($resource_id, $startDate, $endDate);

        // Format this correctly:
        $returnData = [];
        if (!empty($result['graphe']) && !empty($result['graphe']['data'])) {
            $data = $result['graphe']['data'];
            $currentHour = new \DateTime('23:00');

            $end = \count($data);
            for ($i = $end - 1; $i >= $end - 48; $i -= 2) {

                if ($data[$i]['valeur'] == -2 || $data[$i - 1]['valeur'] == -2) {
                    $value = null;
                } else {
                    $value = $data[$i]['valeur'] + $data[$i - 1]['valeur'];
                }

                $returnData[$currentHour->format('H:i')] = $value;
                $currentHour->modify('-60 min');
            }
        }

        $returnData = \array_reverse($returnData);
        return $this->data['hours'][$date->format('d/m/Y')] = $returnData;
    }

    private function getDataPerDay(\DateTime $startDate, \DateTime $endDate)
    {
        // Max 31 days:
        if (($startDate->getTimestamp() - $startDate->getTimestamp()) / 86400 > 31) {
            return [
                'etat' => null, 'error' => 'Max number of days can not exceed 31 days'
            ];
        }

        $resource_id = 'urlCdcJour';
        $result = $this->getData($resource_id, $startDate->format("d/m/Y"), $endDate->format("d/m/Y"));

        // Format this correctly:
        $returnData = [];
        if (!empty($result['graphe']) && !empty($result['graphe']['data'])) {
            $currentDate = clone $startDate;
            foreach ((array)$result['graphe']['data'] as $day) {
                $value = $day['valeur'] != -2 ? $day['valeur'] : null;

                $returnData[$currentDate->format("d/m/Y")] = $value;
                $currentDate->modify('+1 day');
            }
        }

        return $this->data['days'] = $returnData;
    }

    private function getDataPerMonth(\DateTime $startDate, \DateTime $endDate)
    {
        $resource_id = 'urlCdcMois';
        $result = $this->getData($resource_id, $startDate->format("d/m/Y"), $endDate->format("d/m/Y"));

        // Format this correctly:
        $returnData = [];

        if (!empty($result['graphe']) && !empty($result['graphe']['data'])) {
            $currentMonth = clone $startDate;

            foreach ((array)$result['graphe']['data'] as $month) {
                $value = $month['valeur'] != -2 ? $month['valeur'] : null;

                $returnData[$currentMonth->format('M Y')] = $value;
                $currentMonth->modify('+1 month');
            }
        }

        return $this->data['months'] = $returnData;
    }

    private function getDataPerYear()
    {
        $resource_id = 'urlCdcAn';
        $result = $this->getData($resource_id, null, null);

        // Format this correctly:
        $returnData = [];

        if (!empty($result['graphe']) && !empty($result['graphe']['data'])) {
            $data = $result['graphe']['data'];
            $c = \count($data) - 1;
            $currentYear = new \DateTime('1 years ago');

            foreach ((array)$data as $year) {
                $value = $year['valeur'] != -2 ? $year['valeur'] : null;

                $returnData[$currentYear->format('Y')] = $value;
                $currentYear->modify('+1 year');
            }
        }

        return $this->data['years'] = $returnData;
    }

    /**
     * Get data for all frequencies for $date.
     *
     * @param \DateTime $date
     * @return array of data.
     */
    public function getAll(\DateTime $date) : array
    {
        // Get per hour for date:
        $formattedDate = $date->format('d/m/Y');
        $this->getDataPerHour($date);

        // Get per day:
        $var = clone $date;
        $fromMonth = $var->sub(new \DateInterval('P30D'));
        $this->getDataPerDay($fromMonth, $date);

        // Get per month:
        $var = clone $date;
        $fromYear = $var->sub(new \DateInterval('P1Y'));
        $this->getDataPerMonth($fromYear, $date);

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
    private function request($method, $url, $postdata = null)
    {
        if (!isset($this->curlHdl)) {
            $this->curlHdl = \curl_init();
            \curl_setopt($this->curlHdl, CURLOPT_COOKIEJAR, $this->cookFile);
            \curl_setopt($this->curlHdl, CURLOPT_COOKIEFILE, $this->cookFile);

            \curl_setopt($this->curlHdl, CURLOPT_SSL_VERIFYHOST, false);
            \curl_setopt($this->curlHdl, CURLOPT_SSL_VERIFYPEER, false);

            \curl_setopt($this->curlHdl, CURLOPT_HEADER, true);
            \curl_setopt($this->curlHdl, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($this->curlHdl, CURLOPT_FOLLOWLOCATION, true);

            \curl_setopt($this->curlHdl, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0');
        }

        $url = \filter_var($url, FILTER_SANITIZE_URL);
        \curl_setopt($this->curlHdl, CURLOPT_URL, $url);

        if ($method == 'POST') {
            \curl_setopt($this->curlHdl, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($this->curlHdl, CURLOPT_POST, true);
        } else {
            \curl_setopt($this->curlHdl, CURLOPT_POST, false);
        }

        if (isset($postdata)) {
            \curl_setopt($this->curlHdl, CURLOPT_CUSTOMREQUEST, 'POST');
            \curl_setopt($this->curlHdl, CURLOPT_POSTFIELDS, $postdata);
        }

        $response = \curl_exec($this->curlHdl);

        $this->error = null;
        if ($response === false) {
            $this->error = \curl_error($this->curlHdl);
        }

        if ($this->isAuth) {
            $header_size = \curl_getinfo($this->curlHdl, CURLINFO_HEADER_SIZE);
            $response = \substr($response, $header_size);
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

        $postdata = null;
        if ($startDate) {
            $postdata = \http_build_query(
                [
                    '_' . $p_p_id . '_dateDebut' => $startDate,
                    '_' . $p_p_id . '_dateFin' => $endDate
                ]
            );
        }

        $response = $this->request('GET', $url, $postdata);

        return \json_decode($response, true);
    }

    public function auth(string $login, string $password): bool
    {
        $postdata = \http_build_query(
            [
                'IDToken1' => $login,
                'IDToken2' => $password,
                'SunQueryParamsString' => base64_encode('realm=particuliers'),
                'encoded' => 'true',
                'gx_charset' => 'UTF-8'
            ]
        );

        $url = self::LOGIN_BASE_URL . self::API_LOGIN_URL;
        $response = $this->request('POST', $url, $postdata);

        // Connected ?
        \preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = [];
        foreach ($matches[1] as $item) {
            \parse_str($item, $cookie);
            $cookies = \array_merge($cookies, $cookie);
        }
        if (!\array_key_exists('iPlanetDirectoryPro', $cookies)) {
            $this->error = 'Sorry, could not connect. Check your credentials.';
            return false;
        }

        $url = self::API_BASE_URL . '/accueil';
        $response = $this->request('GET', $url);

        return true;
    }
}
