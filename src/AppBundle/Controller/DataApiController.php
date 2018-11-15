<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DataValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataApiController extends Controller
{
    const WEEK_REPARTITION = 'WEEK';
    const YEAR_HORIZONTAL_REPARTITION = 'YEAR_H';
    const YEAR_VERTICAL_REPARTITION = 'YEAR_V';

    /**
     * Get json to build an heatmap graph between two date.
     *
     * @Route("/data/{dataType}/repartition/{repartitionType}/{start}/{end}", name="data-api-repartition")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $repartitionType
     *     Type of repartition we want (week, year_h, year_v)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getRepartionAction(Request $request, $dataType, $repartitionType, $start = NULL, $end = NULL)
    {
        $repartitionType = strtoupper($repartitionType);
        $dataType = strtoupper($dataType);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Set and build axes's type & frequency according to repartitionType.
        list($axe, $axeX, $axeY, $frequency) = $this->buildRepartitionAxes($repartitionType, $start, $end);

        // Get values from Database.
        $values = $this->getRepartitionData($start, $end, $dataType, $axeX, $axeY, $frequency, $repartitionType);

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
     * @Route("/data/{dataType}/evolution/{frequency}/{start}/{end}", name="data-api-evolution")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param string $start
     * @param string $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEvolutionAction(Request $request, $dataType, $frequency, $start = NULL, $end = NULL)
    {
        $frequency = strtoupper($frequency);
        $dataType = strtoupper($dataType);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $result = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $axe = $this->buildEvolutionAxes($frequency, $start, $end);
        $data = $this->buildEvolutionDataObject($result, $frequency, $axe);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get json to build an sum of value graph group by a dataValue column between two date.
     *
     * @Route("/data/{dataType}/sum-group/{frequency}/{groupBy}/{start}/{end}", name="data-api-sum-group-by")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param string $groupBy
     *     The column we want to group by (from dataValue table)
     * @param string $start
     * @param string $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSumGroupByAction(Request $request, $dataType, $frequency, $groupBy, $start = NULL, $end = NULL)
    {
        $frequency = strtoupper($frequency);
        $dataType = strtoupper($dataType);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $result = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getSumValueGroupBy($start, $end, $feedData, DataValue::FREQUENCY[$frequency], $groupBy);

        $axe = (object)[
            'x' => [
                $this->get('translator')->trans('Lun.'),
                $this->get('translator')->trans('Mar.'),
                $this->get('translator')->trans('Mer.'),
                $this->get('translator')->trans('Jeu.'),
                $this->get('translator')->trans('Ven.'),
                $this->get('translator')->trans('Sam.'),
                $this->get('translator')->trans('Dim.'),
            ],
            'label' => [
                $this->get('translator')->trans('Lun.'),
                $this->get('translator')->trans('Mar.'),
                $this->get('translator')->trans('Mer.'),
                $this->get('translator')->trans('Jeu.'),
                $this->get('translator')->trans('Ven.'),
                $this->get('translator')->trans('Sam.'),
                $this->get('translator')->trans('Dim.'),
            ],
        ];
        $data = $this->buildSumGroupByDataObject($result, $axe);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get sum between two date.
     *
     * @Route("/data/{dataType}/sum/{start}/{end}", name="data-api-sum")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSumAction(Request $request, $dataType, $start = NULL, $end = NULL)
    {
        $dataType = strtoupper($dataType);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getSumValue($start, $end, $feedData, DataValue::FREQUENCY['DAY']);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get average by <frequency> between two date.
     *
     * @Route("/data/{dataType}/avg/{frequency}/{start}/{end}", name="data-api-average")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAverageAction(Request $request, $dataType, $frequency, $start = NULL, $end = NULL)
    {
        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getAverageValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get max by <frequency> between two date.
     *
     * @Route("/data/{dataType}/max/{frequency}/{start}/{end}", name="data-api-max")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMaxAction(Request $request, $dataType, $frequency, $start = NULL, $end = NULL)
    {
        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getMaxValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get minimum by <frequency> between two date.
     *
     * @Route("/data/{dataType}/min/{frequency}/{start}/{end}", name="data-api-min")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMinAction(Request $request, $dataType, $frequency, $start = NULL, $end = NULL)
    {
        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getMinValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get number of value by <frequency> between two date.
     *
     * @Route("/data/{dataType}/inf/{value}/{frequency}/{start}/{end}", name="data-api-number")
     *
     * @param Request $request
     * @param string $dataType
     *     Type of data we want (conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getNumberInfAction(Request $request, $dataType, $value, $frequency, $start = NULL, $end = NULL)
    {
        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getNumberInfValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency], $value);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get XY by <frequency> between two date.
     *
     * @Route("/data/xy/{dataTypeX}/{dataTypeY}/{frequency}/{start}/{end}", name="data-api-xy")
     *
     * @param Request $request
     * @param string $dataTypeX
     *     Type of data we want on x axis(conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $dataTypeY
     *     Type of data we want on y axis(conso_elec, temperature, dju, pressure, nebulosity, humidity)
     * @param string $frequency
     *     Frequency we want for the evolution (day, week, month)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getXY(Request $request, $dataTypeX, $dataTypeY, $frequency, $start = NULL, $end = NULL)
    {
        $dataTypeX = strtoupper($dataTypeX);
        $dataTypeY = strtoupper($dataTypeY);
        $frequency = strtoupper($frequency);
        $start = $start ? new \DateTime($start) : new \DateTime('2018-01-01');
        $end = $end ? new \DateTime($end . ' 23:59:59') : new \DateTime();

        // Find feedData with the good dataType.
        $feedDataX = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataTypeX);

        $feedDataY = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataTypeY);

        // Get data between $start & $end for requested frequency.
        $results = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getXY($start, $end, $feedDataX, $feedDataY, DataValue::FREQUENCY[$frequency]);

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
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array of values [$axe, $axeX, $axeY, $frequency]
     */
    private function buildRepartitionAxes($repartitionType, $start, $end)
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
                    $this->get('translator')->trans('Lun.'),
                    $this->get('translator')->trans('Mar.'),
                    $this->get('translator')->trans('Mer.'),
                    $this->get('translator')->trans('Jeu.'),
                    $this->get('translator')->trans('Ven.'),
                    $this->get('translator')->trans('Sam.'),
                    $this->get('translator')->trans('Dim.'),
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
                    $this->get('translator')->trans('Lun.'),
                    $this->get('translator')->trans('Mar.'),
                    $this->get('translator')->trans('Mer.'),
                    $this->get('translator')->trans('Jeu.'),
                    $this->get('translator')->trans('Ven.'),
                    $this->get('translator')->trans('Sam.'),
                    $this->get('translator')->trans('Dim.'),
                ];

                $currentDate = clone $start;
                $endYear =  (int)$end->format('Y');
                $endWeek =  (int)$end->format('W');
                while((int)$currentDate->format('W') <= $endWeek || (int)$currentDate->format('Y') < $endYear) {
                    $axe->x[] = (int)$currentDate->format('W');
                    $axe->year[] = (int)$currentDate->format('Y');
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
                    $this->get('translator')->trans('Lun.'),
                    $this->get('translator')->trans('Mar.'),
                    $this->get('translator')->trans('Mer.'),
                    $this->get('translator')->trans('Jeu.'),
                    $this->get('translator')->trans('Ven.'),
                    $this->get('translator')->trans('Sam.'),
                    $this->get('translator')->trans('Dim.'),
                ];

                $currentDate = clone $start;
                $endYear =  (int)$end->format('Y');
                $endWeek =  (int)$end->format('W');
                while((int)$currentDate->format('W') <= $endWeek || (int)$currentDate->format('Y') < $endYear) {
                    $axe->y[] = $currentDate->format('W');
                    $axe->year[] = (int)$currentDate->format('Y');
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
     * @param \Datetime $start
     * @param \Datetime $end
     * @param string $dataType
     * @param string $axeX
     * @param string $axeY
     * @param string $frequency
     * @param string $repartitionType
     */
    private function getRepartitionData($start, $end, $dataType, $axeX, $axeY, $frequency, $repartitionType)
    {
        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        return $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getRepartitionValue($start, $end, $feedData, $axeX, $axeY, $frequency, $repartitionType);
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
            $data->values[$index] = $value['value'];
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

    private function buildEvolutionAxes($frequency, $start, $end)
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

        $currentDate = clone $start;
        while($currentDate <= $end) {
            $axe->x[] = $currentDate->format($axeFormat);
            $axe->label[] = $currentDate->format($labelFormat);
            $currentDate->add(new \DateInterval($step));
        }

        return $axe;
    }
}
