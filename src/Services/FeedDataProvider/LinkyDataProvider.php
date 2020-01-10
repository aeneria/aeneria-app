<?php

namespace App\Services\FeedDataProvider;


use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;

/**
 * Linky Data Provider
 *
 * @see https://github.com/KiboOst/php-LinkyAPI
 * @todo simplify curl requests
 *
 */
class LinkyDataProvider extends AbstractFeedDataProvider
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

    private $cookFile = '';
    private $curlHdl = null;

    /**
     * {@inheritDoc}
     * @see \App\FeedObject\FeedObject::getFrequencies()
     */
    public static function getFrequencies()
    {
        return DataValue::FREQUENCY;
    }

    /**
     * @inheritdoc
     */
    public static function getParametersName(Feed $feed): array
    {
        return [
            'LOGIN' => 'Adresse email du compte Enedis',
            'PASSWORD' => 'Mot de passe',
        ];
    }

    /**
     * Fetch ENEDIS data for $date and persist its in database.
     *
     * @param \DateTime $date
     */
    public function fetchData(\DateTime $date, array $feeds, bool $force = false)
    {
        foreach ($feeds as $feed) {
            if ( (!$feed instanceof Feed) || $feed->getFeedType() !== 'LINKY') {
                throw new \InvalidArgumentException("Should be an array of Linky Feeds overhere !");
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $this->getFrequencies())) {
                $feedParam = $feed->getParam();

                if ( $this->auth($feedParam['LOGIN'], $feedParam['PASSWORD'])) {

                    $data = $this->getAll($date);
                    $this->persistData($date, $feed, $data);
                }
            }
        }
    }

    /**
     * Persist data in database.
     *
     * @param \DateTime $date
     */
    private function persistData(\DateTime $date, Feed $feed, array $data)
    {
        $date = new \DateTime($date->format("Y-m-d 00:00:00"));

        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);

        // Persist hours data.
        foreach ($data['hours'] as $hour => $value) {
            if ($value && (int)$value !== -1) {
                $this->feedDataRepository->updateOrCreateValue(
                    $feedData,
                    new \DateTime($date->format("Y-m-d") . $hour . ':00'),
                    DataValue::FREQUENCY['HOUR'],
                    $value
                );
            }
        }

        // Persist day data.
        $value = \end($data['days']);
        if ($value && (int)$value !== -1) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY['DAY'],
                $value
            );
        }

        // Persist month data.
        $value = \end($data['months']);
        if ($value && (int)$value !== -1) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY['MONTH'],
                $value
            );
        }

        // Persist year data.
        $value = \end($data['years']);
        if ($value && (int)$value !== -1) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY['YEAR'],
                $value
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
            ->dataValueRepository
            ->getSumValue(
                $firstDayOfWeek,
                $lastDayOfWeek,
                $feedData,
                DataValue::FREQUENCY['DAY']
            );

        if (isset($agregateData[0]['value'])) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $firstDayOfWeek,
                DataValue::FREQUENCY['WEEK'],
                \round($agregateData[0]['value'], 1)
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
            ->dataValueRepository
            ->getSumValue(
                $firstDayOfYear,
                $lastDayOfYear,
                $feedData,
                DataValue::FREQUENCY['MONTH']
            );

        if (isset($agregateData[0]['value'])) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $firstDayOfYear,
                DataValue::FREQUENCY['YEAR'],
                \round($agregateData[0]['value'], 1)
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

        return \array_reverse($returnData);
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

        return $returnData;
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

        return $returnData;
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

        return $returnData;
    }

    /**
     * Get data for all frequencies for $date.
     *
     * @param \DateTime $date
     * @return array of data.
     */
    private function getAll(\DateTime $date) : array
    {
        $data = [];
        // Get per hour for date:
        $data['hours'] = $this->getDataPerHour($date);

        // Get per day:
        $var = clone $date;
        $fromMonth = $var->sub(new \DateInterval('P30D'));
        $data['days'] = $this->getDataPerDay($fromMonth, $date);

        // Get per month:
        $var = clone $date;
        $fromYear = $var->sub(new \DateInterval('P1Y'));
        $data['months'] = $this->getDataPerMonth($fromYear, $date);

        // Get per year:
        $data['years'] = $this->getDataPerYear();

        return $data;
    }

    /**
     * Standard function handling all get/post request with curl | return string
     * @param string $method
     * @param string $url
     * @param array $postdata
     * @return string
     */
    private function request($method, $url, $postdata = null, $withHeader = false)
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

        if (!$withHeader) {
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
        // Reset older connexion
        $this->cookFile = '';
        $this->curlHdl = null;

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
        $response = $this->request('POST', $url, $postdata, true);

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
