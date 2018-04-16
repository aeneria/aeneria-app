<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DataValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataLinkyController extends Controller
{
    /**
     * @Route("/data/linky/repartition/{frequency}", name="data-linky-repartition")
     */
    public function getRepartionAction(Request $request, $repartitionType)
    {
        $var = $request->request;
        $start = $var['start'];
        $end = $var['end'];
        $repartitionType = strtoupper($repartitionType);

        //Find Linky object and feedData that are attached to it
        $linkyFeed = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Feed')
            ->findOneByFeedType('LINKY');

        $consoFeedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByFeed($linkyFeed);

        // Get data between $start & $end for requested frequency
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getValueRepartition($start, $end, $consoFeedData, $repartitionType);

        //@TODO build xValues, yValues & zValues

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
     * @Route("/data/linky/evolution", name="data-linky-evolution")
     */
    public function getEvolutionAction(Request $request, $frequency)
    {
        $var = $request->request;
        $start = $var['start'];
        $end = $var['end'];
        $frequency = strtoupper($frequency);

        //Find Linky object and feedData that are attached to it
        $linkyFeed = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Feed')
            ->findOneByFeedType('LINKY');

        $consoFeedData = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData')
            ->findOneByFeed($linkyFeed);

        // Get data between $start & $end for requested frequency
        $data = $this
            ->getDoctrine()
            ->getRepository('AppBundle:DataValue')
            ->getValue($start, $end, $consoFeedData, DataValue::FREQUENCY[$frequency]);

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
}
