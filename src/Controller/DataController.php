<?php

namespace App\Controller;

use App\Entity\DataValue;
use App\Entity\Place;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataController extends AbstractAppController
{
    const WEEK_REPARTITION = 'WEEK';
    const YEAR_HORIZONTAL_REPARTITION = 'YEAR_H';
    const YEAR_VERTICAL_REPARTITION = 'YEAR_V';

    /** @var FeedDataRepository */
    private $feedDataRepository;

    /** @var DataValueRepository */
    private $dataValueRepository;

    public function __construct(bool $userCanSharePlace, bool $placeCanBePublic, PlaceRepository $placeRepository, FeedDataRepository $feedDataRepository, DataValueRepository $dataValueRepository)
    {
        parent::__construct($userCanSharePlace, $placeCanBePublic, $placeRepository);

        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;
    }

    /**
     * Get json to build an heatmap graph between two date.
     *
     * @Route("/data/{placeId}/repartition/{dataType}/{repartitionType}/{start}/{end}", name="data-api-repartition")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $repartitionType
     *     Type of repartition we want (week, year_h, year_v)
     */
    public function getRepartionAction(string $placeId, string $dataType, string $repartitionType, string $start, string $end, TranslatorInterface $translator): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $repartitionType = strtoupper($repartitionType);
        $dataType = strtoupper($dataType);
        $start = new \DateTimeImmutable($start);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Set and build axes's type & frequency according to repartitionType.
        list($axe, $axeX, $axeY, $frequency) = $this->buildRepartitionAxes($repartitionType, $start, $end, $translator);

        // Get values from Database.
        $values = $this->getRepartitionData($place, $start, $end, $dataType, $axeX, $axeY, $frequency, $repartitionType);

        // Build data object.
        $data = $this->buildRepartitionDataObject($axe, $values, $repartitionType);

        $result =(Object)[
            'axe' => $axe,
            'data' => $data,
        ];

        $jsonResult = json_encode($result);
        return new JsonResponse($jsonResult, 200);
    }

    /**
     * Get json to build an evolution graph between two date.
     *
     * @Route("/data/{placeId}/evolution/{dataType}/{frequency}/{start}/{end}", name="data-api-evolution")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     * @param string $start
     * @param string $end
     */
    public function getEvolutionAction(string $placeId, string $dataType, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $frequency = strtoupper($frequency);
        $dataType = strtoupper($dataType);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $result = $this->dataValueRepository->getValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $axe = $this->buildEvolutionAxes($frequency, $start, $end);
        $data = $this->buildEvolutionDataObject($result, $frequency, $axe);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get json to build an sum of value graph group by a dataValue column between two date.
     *
     * @Route("/data/{placeId}/sum-group/{dataType}/{frequency}/{groupBy}/{start}/{end}", name="data-api-sum-group-by")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     * @param string $groupBy
     *     The column we want to group by (from dataValue table)
     */
    public function getSumGroupByAction(string $placeId, string $dataType, string $frequency, string $groupBy, string $start, string $end, TranslatorInterface $translator): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $frequency = strtoupper($frequency);
        $dataType = strtoupper($dataType);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');


        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $result = $this->dataValueRepository->getSumValueGroupBy($start, $end, $feedData, DataValue::FREQUENCY[$frequency], $groupBy);

        $axe = (object)[
            'x' => [
                $translator->trans('Lun.'),
                $translator->trans('Mar.'),
                $translator->trans('Mer.'),
                $translator->trans('Jeu.'),
                $translator->trans('Ven.'),
                $translator->trans('Sam.'),
                $translator->trans('Dim.'),
            ],
            'label' => [
                $translator->trans('Lun.'),
                $translator->trans('Mar.'),
                $translator->trans('Mer.'),
                $translator->trans('Jeu.'),
                $translator->trans('Ven.'),
                $translator->trans('Sam.'),
                $translator->trans('Dim.'),
            ],
        ];
        $data = $this->buildSumGroupByDataObject($result, $axe);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get sum between two date.
     *
     * @Route("/data/{placeId}/sum/{dataType}/{start}/{end}", name="data-api-sum")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     */
    public function getSumAction(string $placeId, string $dataType, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataType = strtoupper($dataType);
        $start = new \DateTimeImmutable($start);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this->dataValueRepository->getSumValue($start, $end, $feedData, DataValue::FREQUENCY['DAY']);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get average by <frequency> between two date.
     *
     * @Route("/data/{placeId}/avg/{dataType}/{frequency}/{start}/{end}", name="data-api-average")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     */
    public function getAverageAction(string $placeId, string $dataType, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this->dataValueRepository->getAverageValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get max by <frequency> between two date.
     *
     * @Route("/data/{placeId}/max/{dataType}/{frequency}/{start}/{end}", name="data-api-max")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     */
    public function getMaxAction(string $placeId, string $dataType, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this->dataValueRepository->getMaxValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get minimum by <frequency> between two date.
     *
     * @Route("/data/{placeId}/min/{dataType}/{frequency}/{start}/{end}", name="data-api-min")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     */
    public function getMinAction(string $placeId, string $dataType, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this->dataValueRepository->getMinValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get number of value inferior of <value> by <frequency> between two date.
     *
     * @Route("/data/{placeId}/inf/{dataType}/{value}/{frequency}/{start}/{end}", name="data-api-number")
     *
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want (day, week, month)
     */
    public function getNumberInfAction(string $placeId, string $dataType, string $value, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this->dataValueRepository->getNumberInfValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency], $value);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get XY by <frequency> between two date.
     *
     * @Route("/data/{placeId}/xy/{dataTypeX}/{dataTypeY}/{frequency}/{start}/{end}", name="data-api-xy")
     *
     * @param string $dataTypeX
     *     Type of data we want on x axis(conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $dataTypeY
     *     Type of data we want on y axis(conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     */
    public function getXY(string $placeId, string $dataTypeX, string $dataTypeY, string $frequency, string $start, string $end): JsonResponse
    {
        $place = $this->canSeePlace($placeId);

        $dataTypeX = strtoupper($dataTypeX);
        $dataTypeY = strtoupper($dataTypeY);
        $frequency = strtoupper($frequency);
        $start = DataValue::adaptToFrequency(new \DateTimeImmutable($start), DataValue::FREQUENCY[$frequency]);
        $end = new \DateTimeImmutable($end . ' 23:59:59');

        // Find feedData with the good dataType.
        $feedDataX = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataTypeX);
        $feedDataY = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataTypeY);

        // Get data between $start & $end for requested frequency.
        $results = $this->dataValueRepository->getXY($start, $end, $feedDataX, $feedDataY, DataValue::FREQUENCY[$frequency]);

        $data = (object)[
            'axeX' => [],
            'axeY' => [],
            'date' => []
        ];

        $dateFormat = '';
        switch ($frequency) {
            case 'DAY':
                $dateFormat = 'l d/m/Y';
                break;
            case 'WEEK':
                $dateFormat = 'd/m/Y';
                break;
            case 'MONTH':
                $dateFormat = 'M Y';
                break;
            case 'YEAR':
                $dateFormat = 'Y';
        }

        foreach ($results as $result) {
            $data->axeX[] = $result['xValue'];
            $data->axeY[] = $result['yValue'];
            $data->date[] = $result['date']->format($dateFormat);
        }

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Build axes for a repartition graph
     *
     * @param string $repartitionType
     * @return array of values [$axe, $axeX, $axeY, $frequency]
     */
    private function buildRepartitionAxes(string $repartitionType, \DateTimeInterface $start, \DateTimeInterface $end, TranslatorInterface $translator): array
    {
        $axe = (object)[
            'x' => [],
            'y' => [],
        ];

        switch ($repartitionType) {
            case self::WEEK_REPARTITION:
                $axeX = 'weekDay';
                $axeY = 'hour';
                $frequency = DataValue::FREQUENCY['HOUR'];

                // Build axes.
                $axe->x = [
                    $translator->trans('Lun.'),
                    $translator->trans('Mar.'),
                    $translator->trans('Mer.'),
                    $translator->trans('Jeu.'),
                    $translator->trans('Ven.'),
                    $translator->trans('Sam.'),
                    $translator->trans('Dim.'),
                ];

                for($i = 0; $i<=24; $i++) {
                    $axe->y[$i] = sprintf("%02d", $i) . 'h';
                }
                //$axe->y = array_reverse($axe->y);
                break;

            case self::YEAR_HORIZONTAL_REPARTITION:
                // We also store year for future treatment.
                $axe->year = [];
                $axeX = 'week';
                $axeY = 'weekDay';
                $frequency = DataValue::FREQUENCY['DAY'];

                // Build axes.
                $axe->y = [
                    $translator->trans('Lun.'),
                    $translator->trans('Mar.'),
                    $translator->trans('Mer.'),
                    $translator->trans('Jeu.'),
                    $translator->trans('Ven.'),
                    $translator->trans('Sam.'),
                    $translator->trans('Dim.'),
                ];

                $currentDate = \DateTime::createFromImmutable($start);
                $endWeek = \DateTime::createFromImmutable($end);
                $endWeek->add(new \DateInterval('P'.(7-$end->format('w')).'D'));
                while($currentDate <= $endWeek) {
                    $axe->x[] = (int)$currentDate->format('W');
                    $axe->year[] = (int)$currentDate->format('o');
                    $currentDate->add(new \DateInterval('P1W'));
                }
                break;

            case self::YEAR_VERTICAL_REPARTITION:
                // We also store year for future treatment.
                $axe->year = [];
                $axeX = 'week';
                $axeY = 'weekDay';
                $frequency = DataValue::FREQUENCY['DAY'];

                // Build axes.
                $axe->x = [
                    $translator->trans('Lun.'),
                    $translator->trans('Mar.'),
                    $translator->trans('Mer.'),
                    $translator->trans('Jeu.'),
                    $translator->trans('Ven.'),
                    $translator->trans('Sam.'),
                    $translator->trans('Dim.'),
                ];

                $currentDate = \DateTime::createFromImmutable($start);
                $endWeek = \DateTime::createFromImmutable($end);
                $endWeek->add(new \DateInterval('P'.(7-$end->format('w')).'D'));
                while($currentDate <= $endWeek) {
                    $axe->y[] = (int)$currentDate->format('W');
                    $axe->year[] = (int)$currentDate->format('o');
                    $currentDate->add(new \DateInterval('P1W'));
                }
                break;

            default:
                return NULL;
        }

        return [$axe, $axeX, $axeY, $frequency];
    }

    /**
     * Get data for a repartition graph from database
     */
    private function getRepartitionData(Place $place, \DateTimeImmutable $start, \DateTimeImmutable $end, string $dataType, string $axeX, string $axeY, string $frequency, string $repartitionType): array
    {
        // Find feedData with the good dataType.
        $feedData = $this->feedDataRepository->findOneByPlaceAndDataType($place, $dataType);

        // Get data between $start & $end for requested frequency.
        return $this->dataValueRepository->getRepartitionValue($start, $end, $feedData, $axeX, $axeY, $frequency, $repartitionType);
    }

    private function buildRepartitionDataObject($axe, $values, $repartitionType)
    {
        switch ($repartitionType) {
            case self::WEEK_REPARTITION:
                return $this->buildWeekRepartitionDataObject($axe, $values);
                break;

            case self::YEAR_HORIZONTAL_REPARTITION:
                return $this->buildYearRepartitionDataObject($axe, $values);
                break;

            default:
                $axeReverse = (object)[
                    'x' => [],
                    'y' => [],
                    'year' => [],
                ];

                $axeReverse->x = $axe->y;
                $axeReverse->y = $axe->x;
                $axeReverse->year = $axe->year;
                return $this->buildYearRepartitionDataObject($axeReverse, $values);
        }
    }

    private function buildWeekRepartitionDataObject($axe, $values)
    {
        $data = (object)[
            'values' => [],
            'dates' => [],
        ];

        // Initialize data object with empty values and build dates.
        foreach ($axe->x as $xKey => $xValue) {
            foreach ($axe->y as $yKey => $yValue) {
                $index = $xKey * (count($axe->y)-1) + $yKey;
                if ($yKey<24) {
                    $data->values[$index] = '';
                    // Date will be for example 'monday 12h->13h'.
                    $data->dates[$index] = $xValue . ' ' . $yValue . ' -> ' . $axe->y[$yKey + 1];
                }
            }
        }

        // Fill data object with values from database.
        foreach ($values as $value) {
            $xKey = $value['axeX'];
            $yKey = $value['axeY'];
            $index = $xKey * (count($axe->y) - 1) + $yKey;
            // We store the value in the object.
            $data->values[$index] = $value['value'];
        }

        return $data;
    }

    private function buildYearRepartitionDataObject($axe, $values)
    {
        $data = (object)[
            'values' => [],
            'dates' => [],
        ];

        // Initialize data object with empty values and build dates.
        foreach ($axe->x as $xKey => $xValue) {
            foreach ($axe->y as $yKey => $yValue) {
                $index = $xKey * count($axe->y) + $yKey;
                $data->values[$index] = '';
                // We rebuild the datetime, will be for example '13/02/2018'.
                $currentDate = new \DateTime();
                $currentDate->setISODate($axe->year[$xKey], $xValue, $yKey + 1);
                $data->dates[$index] = $currentDate->format('d/m/y');
            }
        }

        // Fill data object with values from database.
        foreach ($values as $value) {
            $currentDate = new \DateTime();
            $currentDate->setISODate($value['year'], $value['axeX'], $value['axeY'] + 1);
            $currentDate = $currentDate->format('d/m/y');
            $index = array_search($currentDate, $data->dates);

            // We store the value in the object.
            if ($index !== FALSE) {
                $data->values[$index] = $value['value'];
            }
        }
        return $data;
    }

    private function buildEvolutionDataObject($results, $frequency, $axe)
    {
        $data = (object)[
            'label' => $axe->label,
            'axeX' => $axe->x,
            'axeY' => [],
        ];

        switch ($frequency) {
            case 'HOUR':
               $axeFormat = 'd/m/Y H:i';
               break;
            case 'DAY':
                $axeFormat = 'd/m/Y';
                break;
            case 'WEEK':
                $axeFormat = 'd/m/Y';
                break;
            case 'MONTH':
                $axeFormat = 'M Y';
                break;
            case 'YEAR':
                $axeFormat = 'Y';
        }

        foreach ($results as $result) {
            $index = array_search($result->getDate()->format($axeFormat), $axe->x);
            $data->axeY[$index] = $result->getValue();
        }

        foreach (array_keys($axe->x) as $key) {
            if (!isset($data->axeY[$key]))
                $data->axeY[$key] = 0;
        }

        ksort($data->axeY);

        return $data;
    }

    private function buildSumGroupByDataObject($results, $axe)
    {
        $data = (object)[
            'label' => $axe->label,
            'axeX' => $axe->x,
            'axeY' => [],
        ];

        foreach ($results as $result) {
            $index = $result['groupBy'];
            $data->axeY[$index] = $result['value'];
        }

        foreach (array_keys($axe->x) as $key) {
            if (!isset($data->axeY[$key]))
                $data->axeY[$key] = 0;
        }

        ksort($data->axeY);

        return $data;
    }

    private function buildEvolutionAxes(string $frequency, \DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        $axe = (object)[
            'x' => [],
            'label' => [],
        ];
        $axeFormat = '';
        $step = '';

        switch ($frequency) {
            case 'HOUR':
                $axeFormat = 'd/m/Y H:i';
                $labelFormat = 'l d/m/Y H:i';
                $step = 'P1H';
                break;
            case 'DAY':
                $axeFormat = 'd/m/Y';
                $labelFormat = 'l d/m/Y';
                $step = 'P1D';
                break;
            case 'WEEK':
                $axeFormat = 'd/m/Y';
                $labelFormat = 'd/m/Y';
                $step = 'P1W';
                break;
            case 'MONTH':
                $axeFormat = 'M Y';
                $labelFormat = 'M Y';
                $step = 'P1M';
                break;
            case 'YEAR':
                $axeFormat = 'Y';
                $labelFormat = 'Y';
                $step = 'P1Y';
        }

        $currentDate = \DateTime::createFromImmutable($start);
        while($currentDate <= $end) {
            $axe->x[] = $currentDate->format($axeFormat);
            $axe->label[] = $currentDate->format($labelFormat);
            $currentDate->add(new \DateInterval($step));
        }

        return $axe;
    }
}
