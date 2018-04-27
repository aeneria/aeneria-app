<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DataValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     *     Type of repartition we want (week, year)
     * @param \DateTime $start
     * @param \Datetime $end
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getRepartionAction(Request $request, $dataType, $repartitionType, $start, $end)
    {
        $repartitionType = strtoupper($repartitionType);
        $dataType = strtoupper($dataType);

        // Find feedData with the good dataType.
        $feedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByDataType($dataType);

        // Get data between $start & $end for requested frequency.
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getValueRepartition($start, $end, $feedData, $repartitionType);

        //@TODO build xValues, yValues & zValues.

        $xValues = ['Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.', 'Dim.'];

        $yValues = [];
        for($i = 0; $i<24; $i++) {
            $yValues[2*$i] = sprintf("%02d", $i) . 'h';
            $yValues[2*$i + 1] = sprintf("%02d", $i) . 'h30';
        }
        $zValues = [];
        foreach($xValues as $xKey => $xValue) {
            foreach($yValues as $yKey => $yValue){
                $zValues[$yKey][$xKey] = random_int(0, 50);
            }
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
                'marker' => (Object)['size' => 16],
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
    public function getEvolutionAction(Request $request, $dataType, $frequency, $start, $end)
    {
        $frequency = strtoupper($frequency);
        $dataType = strtoupper($dataType);

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
    public function getEvolutionAction(Request $request, $dataType, $start, $end)
    {
        $dataType = strtoupper($dataType);

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
    public function getEvolutionAction(Request $request, $dataType, $frequency, $start, $end)
    {
        $dataType = strtoupper($dataType);
        $frequency = strtoupper($frequency);

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
