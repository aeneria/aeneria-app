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
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {
        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $linkyForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_linky', LinkyType::class);

        /** @var \Symfony\Component\Form\FormBuilder $meteoFranceForm */
        $meteoFranceForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_meteo_france', MeteoFranceType::class);

        if('POST' === $request->getMethod()) {
            if ($request->request->has('form_linky')) {
              // handle the first form
            }

            if ($request->request->has('form_meteo_france')) {
              // handle the second form
            }
        }

        return $this->render('default/config.html.twig', array(
            'form_linky' => $linkyForm->getForm()->createView(),
            'form_meteo_france' => $meteoFranceForm->getForm()->createView(),
        ));
    }
}
