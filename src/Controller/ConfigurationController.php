<?php

namespace App\Controller;

use App\Form\PlaceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends AbstractController
{
    /**
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {

        // We get a Place if it already exists.
        /** @var \App\Entity\Place $place */
        $places = $this
            ->getDoctrine()
            ->getRepository('App:Place')
            ->findAll()
        ;

        if (count($places)) {
            $place = $places[0];
        }
        else {
            $place = null;
        }

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_config', PlaceType::class, $place, ['data_class' => null])
            ->getForm();

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($this->getDoctrine()->getManager(), $configForm->getData());
                $message = 'Votre configuration a bien été enregistrée !';
            }
        }

        return $this->render('pages/configuration.html.twig', [
            'form_config' => $configForm->createView(),
            'message' => !empty($message) ? $message : NULL,
        ]);
    }
}
