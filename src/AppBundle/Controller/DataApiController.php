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
     * Get json to build an heatmap graph between to date.
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
        $end = $end ? new \DateTime($end) : new \DateTime();

        // Set and build axes's type & frequency according to repartitionType.
        list($axe, $axeX, $axeY, $frequency) = $this->buildRepartitionAxes($repartitionType, $start, $end);

        // Get values from Database.
        $values = $this->getRepartitionData($start, $end, $dataType, $axeX, $axeY, $frequency, $repartitionType);

        // Build data object.
        $data = $this->buildRepartitionDataObject($axe, $values, $repartitionType);

        $result = [
            (Object)[
                'axe' => $axe,
                'data' => $data,
            ]
        ];

        $jsonResult = json_encode($result);
        return new JsonResponse($jsonResult, 200);
    }

    /**
     * Get json to build an evolution graph between to date.
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
        $end = $end ? new \DateTime($end) : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        //@TODO build xValues & yValues
        $xValues = [];
        $yValues = [];

        $data = [
            (Object)[
                'x' => $xValues,
                'y' => $yValues,
                'type' => 'bar',
                'showscale' => FALSE,
            ]
        ];

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Get sum between to date.
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
        $end = $end ? new \DateTime($end) : new \DateTime();

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
     * Get average by <frequency> between to date.
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
        $end = $end ? new \DateTime($end) : new \DateTime();

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getAvgValue($start, $end, $feedData, DataValue::FREQUENCY[$frequency]);

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
    }

    /**
     * Build axes for a reparttition graph
     *
     * @param string $repartitionType
     * @param DateTime $start
     * @param DateTime $end
     * @return array of values [$axe, $axeX, $axeY, $frequency]
     */
    private function buildRepartitionAxes($repartitionType, $start, $end) {
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
                $axe->y = array_reverse($axe->y);
                break;

            case self::YEAR_HORIZONTAL_REPARTITION:
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
                while($currentDate <= $end) {
                  $axe->x[] = $currentDate->format('W');
                  $currentDate->add(new \DateInterval('P1W'));
                }
                $axe->x = array_reverse($axe->x);
                break;

            case self::YEAR_VERTICAL_REPARTITION:
                $axeX = 'weekDay';
                $axeY = 'week';
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
                while($currentDate <= $end) {
                  $axe->y[] = $currentDate->format('W');
                  $currentDate->add(new \DateInterval('P1W'));
                }
                $axe->y = array_reverse($axe->y);
                break;

            default:
                return NULL;
        }

        return [$axe, $axeX, $axeY, $frequency];
    }

    /**
     * Get data for a repartition graph from database
     * @param Datetime $start
     * @param Datetime $end
     * @param string $dataType
     * @param string $axeX
     * @param string $axeY
     * @param string $frequency
     * @param string $repartitionType
     */
    private function getRepartitionData($start, $end, $dataType, $axeX, $axeY, $frequency, $repartitionType) {
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

    private function buildRepartitionDataObject($axe, $values, $repartitionType) {
        $data = (object)[
            'values' => [],
            'dates' => [],
        ];

        // Initialize data object with NULL.
        foreach ($axe->x as $xKey => $xValue) {
            foreach ($axe->y as $yKey => $yValue) {
                $index = $xKey * length($axe->y) + $yKey;
                $data->values[$index] = NULL;
                $data->dates[$index] = NULL;
            }
        }

        // Fill data object with values from database.
        foreach ($values as $value) {
            $xKey = array_search($value['axeX'], $axe->x);
            $yKey = array_search($value['axeY'], $axe->y);
            $index = $xKey * length($axe->y) + $yKey;

            // We store the value in the object.
            $data->values[$index] = $value['value'];

            // And the date according to the repartion type :
            //  for a week repartion, date will be for example 'monday 12h->13h'.
            //  for a year repartion, date will be for example '13/02/2018'.
            if ($repartitionType === self::WEEK_REPARTITION) {
                $data->dates[$index] = $axe->x[$xKey] . ' ' . $axe->x[$xKey] . ' -> ' . $axe->x[$xKey + 1];
            }
            elseif ($repartitionType === self::YEAR_HORIZONTAL_REPARTITION) {
                // We rebuild the datetime
                $currentDate = new \DateTime();
                $currentDate->setISODate($value['year'], $value['axeX'], $value['axeY'] + 1);
                $data->dates[$index] = $currentDate->format('d/m/y');
            }
            elseif ($repartitionType === self::YEAR_VERTICAL_REPARTITION) {
                // We rebuild the datetime
                $currentDate = new \DateTime();
                $currentDate->setISODate($value['year'], $value['axeY'], $value['axeX'] + 1);
                $data->dates[$index] = $currentDate->format('d/m/y');
            }
        }
    }
}
