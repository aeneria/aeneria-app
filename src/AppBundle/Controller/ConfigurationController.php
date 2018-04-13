<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\LinkyType;
use AppBundle\Form\MeteoFranceType;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configuration/config", name="config_install")
     */
    public function configAction(Request $request)
    {
        $linkyForm = $this
            ->get('form.factory')
            ->createNamedBuilder(LinkyType::class,'form_linky');
        $meteoFranceForm = $this
            ->get('form.factory')
            ->createNamedBuilder(MeteoFranceType::class, 'form_meteo_france');

        if('POST' === $request->getMethod()) {

            if ($request->request->has('form_linky')) {
              // handle the first form
            }

            if ($request->request->has('form_meteo_france')) {
              // handle the second form
            }
        }

        return $this->render('default/config.html.twig', array(
          'form_linky' => $linkyForm->createView(),
          'form_meteo_france' => $meteoFranceForm->createView(),
        ));
    }
}
