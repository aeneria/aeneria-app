<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DataValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataApiController extends Controller
{
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
        if ($repartitionType === 'WEEK') {
            $axeX = 'weekDay';
            $axeY = 'hour';
            $frequency = DataValue::FREQUENCY['HOUR'];

            // Build axes.
            $xValues = [
                $this->get('translator')->trans('Lun.'),
                $this->get('translator')->trans('Mar.'),
                $this->get('translator')->trans('Mer.'),
                $this->get('translator')->trans('Jeu.'),
                $this->get('translator')->trans('Ven.'),
                $this->get('translator')->trans('Sam.'),
                $this->get('translator')->trans('Dim.'),
            ];

            $yValues = [];
            for($i = 0; $i<24; $i++) {
                $yValues[$i] = sprintf("%02d", $i) . 'h';
            }
            $yValues = array_reverse($yValues);
        }
        elseif ($repartitionType === 'YEAR_H') {
            $axeX = 'week';
            $axeY = 'weekDay';
            $frequency = DataValue::FREQUENCY['DAY'];

            // Build axes.
            $yValues = [
                $this->get('translator')->trans('Lun.'),
                $this->get('translator')->trans('Mar.'),
                $this->get('translator')->trans('Mer.'),
                $this->get('translator')->trans('Jeu.'),
                $this->get('translator')->trans('Ven.'),
                $this->get('translator')->trans('Sam.'),
                $this->get('translator')->trans('Dim.'),
            ];

            $xValues = [];
            $currentDate = clone $start;
            while($currentDate <= $end) {
                $xValues[] = $currentDate->format('W');
                $currentDate->add(new \DateInterval('P1W'));
            }
            $xValues = array_reverse($xValues);
        }
        elseif ($repartitionType === 'YEAR_V') {
            $axeX = 'weekDay';
            $axeY = 'week';
            $frequency = DataValue::FREQUENCY['DAY'];

            // Build axes.
            $xValues = [
                $this->get('translator')->trans('Lun.'),
                $this->get('translator')->trans('Mar.'),
                $this->get('translator')->trans('Mer.'),
                $this->get('translator')->trans('Jeu.'),
                $this->get('translator')->trans('Ven.'),
                $this->get('translator')->trans('Sam.'),
                $this->get('translator')->trans('Dim.'),
            ];

            $yValues = [];
            $currentDate = clone $start;
            while($currentDate <= $end) {
                $yValues[] = $currentDate->format('W');
                $currentDate->add(new \DateInterval('P1W'));
            }
            $yValues = array_reverse($yValues);
        }
        else {
            return NULL;
        }

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getRepartitionValue($start, $end, $feedData, $axeX, $axeY, $frequency);

        $zValues = [];
        foreach ($xValues as $xKey => $xValue) {
            foreach ($yValues as $yKey => $yValue) {
                if (!isset($zValues[$xKey][$yValue])) {
                    $zValues[$xKey][$yKey] = NULL;
                }
            }
        }

        foreach ($data as $value) {
            $y = array_keys($yValues, $value['axeY'])[0];
            $zValues[$value['axeX']][$y]= $value['value'];
        }

        $data = [
            (Object)[
                'x' => $xValues,
                'y' => $yValues,
                'z' => $zValues,
                'type' => 'heatmap',
                'showscale' => FALSE,
                'mode' => 'markers',
                'hoverinfo' => 'z',
                'marker' => (Object)['size' => 4],
                'colorscale' => [
                    [0, '#f6ffcd'],
                    [1, '#6ba083']
                ]
            ]
        ];

        $jsonData = json_encode($data);
        return new JsonResponse($jsonData, 200);
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
     * @param \DateTime $start
     * @param \Datetime $end
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
     * @Route("/data/{dataType}/sum/{start}/{end}", name="data-api-evolution")
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
     * @Route("/data/{dataType}/avg/{frequency}/{start}/{end}", name="data-api-evolution")
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
}
