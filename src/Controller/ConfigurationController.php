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
            ->findByUser($this->getUser())
        ;

        return $this->render('configuration/configuration.html.twig', [
            'places' => $places
        ]);
    }

    /**
     * @Route("/configuration/place/add", name="config.place.add")
     */
    public function placeAddAction(Request $request)
    {
        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, null, [
                'data_class' => null
            ])
        ;

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($this->getDoctrine()->getManager(), $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'La nouvelle adresse a bien été enregistrée !');
                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/update", name="config.place.update")
     */
    public function placeUpdateAction(Request $request, string $id)
    {
        /** @var \App\Entity\Place $place */
        $place = $this
            ->getDoctrine()
            ->getRepository('App:Place')
            ->find($id)
        ;

        // @todo sécurité ici !

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, $place, [
                'data_class' => null
            ])
        ;

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($this->getDoctrine()->getManager(), $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'Votre configuration a bien été enregistrée !');
                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/delete", name="config.place.delete")
     */
    public function placeDeleteAction(Request $request, string $id)
    {
        /** @var \App\Entity\Place $place */
        $place = $this
            ->getDoctrine()
            ->getRepository('App:Place')
            ->find($id)
        ;

        // @todo sécurité ici !

        // ça va un peu vite nan ?
        $place = $this
            ->getDoctrine()
            ->getRepository('App:Place')
            ->purge($place)
        ;

        return $this->redirectToRoute('config');
    }
}
