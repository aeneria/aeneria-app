<?php

namespace App\Controller;

use App\Entity\Feed;
use App\Entity\FeedData;
use App\Form\EnedisFeedType;
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

        // We get the Linky Feed if it already exists.
        /** @var \App\Entity\Feed $linky */
        $linky = $this
            ->getDoctrine()
            ->getRepository('App:Feed')
            ->findOneByFeedType('LINKY');

        // We get the Linky Feed if it already exists.
        /** @var \App\Entity\Feed $linky */
        $meteoFrance = $this
            ->getDoctrine()
            ->getRepository('App:Feed')
            ->findOneByFeedType('METEO_FRANCE');

        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $configForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_config', EnedisFeedType::class, [$linky, $meteoFrance])
            ->getForm();

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                EnedisFeedType::handleSubmit($this->getDoctrine()->getManager(), [$linky, $meteoFrance], $configForm->getData());
                $message = 'Votre configuration a bien été enregistrée !';
            }
        }

        return $this->render('pages/configuration.html.twig', [
            'form_config' => $configForm->createView(),
            'message' => !empty($message) ? $message : NULL,
        ]);
    }
}
