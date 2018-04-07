<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataApiController extends Controller
{
    const FEED_TYPES = [
        'LINKY' => [
            'ID' => 1,
            'NAME' => 'Linky',
            'PARAM' => ['LOGIN', 'PASSWORD'],
            'DATA_TYPE' => [
                'CONSO_ELEC' => [
                    'UNIT' => 'KWh',
                ]
            ],
            'FETCH_CALLBACK' => 'fetchLinkyData',
        ],
        'METEO_FRANCE' => [
            'ID' => 2,
            'NAME' => 'Meteo France',
            'PARAM' => ['LOGIN', 'PASSWORD', 'TOKEN'],
            'DATA_TYPE' => [
                'TEMPERATURE' => [
                    'UNIT' => '°C',
                ],
                'DJU' => [
                    'UNIT' => 'DJU',
                ],
                'PRESSURE' => [
                    'UNIT' => 'hPa',
                ],
                'HUMIDITY' => [
                    'UNIT' => '%',
                ],
                'NEBULOSITY' => [
                    'UNIT' => '%',
                ],
            ],
            'FETCH_CALLBACK' => 'fetchMeteoFranceData',
        ],
    ];
    
    const FREQUENCY = [
        'HOUR' => 1,
        'DAY' => 2,
        'WEEK' => 3,
        'MONTH' => 4,
        'YEAR' => 5,
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