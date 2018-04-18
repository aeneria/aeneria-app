<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Feed;
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
        list($linkyForm, $linky) = $this->prepareLinkyForm();

        /** @var \Symfony\Component\Form\FormBuilder $meteoFranceForm */
        list($meteoFranceForm, $meteoFrance) = $this->prepareMeteoFranceForm();

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

    /**
     * Prepare the Linky form & get existing Linky feed.
     *
     * @return [\Symfony\Component\Form\FormBuilder, \AppBundle\Entity\Feed]
     */
    private function prepareLinkyForm() {
        // We get the Linky Feed if it already exists.
        /** @var \AppBundle\Entity\Feed $linky */
        $linky = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Feed')
            ->findOneByType('LINKY');

        // We set defaultValue if there's alreadya linky feed.
        $defaultValue= [];
        if ($linky) {
            $defaultValue['name'] = $linky->getName();
            $param = json_decode($linky->getParam());
            foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $paramName) {
                $defaultValue[strtolower($paramName)] = $param[$paramName];
            }
        }

        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $linkyForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_linky', LinkyType::class, $defaultValue)
            ->getForm();

        return [$linkyForm, $linky];
    }

    /**
     * Prepare the MeteoFrance form & get existing MeteoFrance feed.
     *
     * @return [\Symfony\Component\Form\FormBuilder, \AppBundle\Entity\Feed]
     */
    private function prepareMeteoFranceForm() {
        // We get the Linky Feed if it already exists.
        /** @var \AppBundle\Entity\Feed $linky */
        $meteoFrance = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Feed')
            ->findOneByType('METEO_FRANCE');

        // We set defaultValue if there's alreadya linky feed.
        $defaultValue= [];
        if ($meteoFrance) {
            $defaultValue['name'] = $meteoFrance->getName();
            $param = json_decode($meteoFrance->getParam());
            $defaultValue['station'] = $param['STATION_ID'];
        }

        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $meteoFranceForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_linky', LinkyType::class, $defaultValue)
            ->getForm();

        return [$meteoFranceForm, $meteoFrance];
    }
}
