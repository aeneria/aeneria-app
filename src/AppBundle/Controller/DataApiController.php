<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataApiController extends Controller
{
    public static $FEED_TYPES = [
        'LINKY' => [
            'param' => ['LOGIN', 'PASSWORD'],
            'dataType' => [
                'CONSO_ELEC' => [
                    'unit' => 'KWh',
                ]
            ]
        ],
        'METEO_FRANCE' => [
            'param' => ['LOGIN', 'PASSWORD', 'TOKEN'],
            'dataType' => [
                'TEMPERATURE' => [
                    'unit' => '°C',
                ],
                'DJU' => [
                    'unit' => 'DJU',
                ],
                'PRESSURE' => [
                    'unit' => 'hPa',
                ],
                'HUMIDITY' => [
                    'unit' => '%',
                ],
                'NEBULOSITY' => [
                    'unit' => '%',
                ],
            ],
        ],
    ];
    
    /**
     * @Route("/data/week-repartition", name="data-week-repartition")
     */
    public function getWeekRepartionAction(Request $request)
    {
        $var = $request->request;
        
        $xValues = ['Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.', 'Dim.'];
        
        $yValues = [];
        for($i = 0; $i<24; $i++) {
            $yValues[$i] = sprintf("%02d", $i) . 'h';
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
}