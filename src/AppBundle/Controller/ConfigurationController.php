<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Feed;
use AppBundle\Form\LinkyType;
use AppBundle\Form\MeteoFranceType;
use AppBundle\Entity\FeedData;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {
        /** @var \Symfony\Component\Form\Form $linkyForm */
        list($linkyForm, $linky) = $this->prepareLinkyForm();

        /** @var \Symfony\Component\Form\Form $meteoFranceForm */
        list($meteoFranceForm, $meteoFrance) = $this->prepareMeteoFranceForm();

        if('POST' === $request->getMethod()) {
            if ($request->request->has('form_linky')) {
                $linkyForm->handleRequest($request);
                if ($linkyForm->isValid()) {
                    $this->persistLinky($linky, $linkyForm);
                }
            }
            elseif ($request->request->has('form_meteo_france')) {
                $meteoFranceForm->handleRequest($request);
                if ($meteoFranceForm->isValid()) {
                    $this->persistMeteoFrance($meteoFrance, $meteoFranceForm);
                }
            }
        }

        return $this->render('bases/config.html.twig', array(
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
            ->findOneByFeedType('LINKY');

        // We set defaultValue if there's already a linky feed.
        $defaultValue= [];
        if ($linky) {
            $defaultValue['name'] = $linky->getName();
            $param = $linky->getParam();
            foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $paramName => $label) {
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
            ->findOneByFeedType('METEO_FRANCE');

        // We set defaultValue if there's alreadya linky feed.
        $defaultValue= [];
        if ($meteoFrance) {
            $defaultValue['name'] = $meteoFrance->getName();
            $param = $meteoFrance->getParam();
            $defaultValue['station'] = (int)$param['STATION_ID'];
        }

        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $meteoFranceForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_meteo_france', MeteoFranceType::class, $defaultValue)
            ->getForm();

        return [$meteoFranceForm, $meteoFrance];
    }

    /**
     * Persist the Liny Feed in DB and create dependent FeedData
     *
     * @param Feed $linky
     * @param Form $linkyForm
     */
    private function persistLinky(Feed &$linky = NULL, Form $linkyForm) {
        if(!$linky) {
            $linky = new Feed();
            $linky->setFeedType('LINKY');
            $linky->setCreator('admin'); //@TODO Get yunohost user
            $linky->setPublic(TRUE); //@TODO Deal with yunohost users
        }
        $data = $linkyForm->getData();

        $linky->setName($data['name']);
        $param = [];
        foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
            $param[$name] = $data[strtolower($name)];
        }
        $linky->setParam($param);
        $this->createDependentFeedData($linky);

        $this->getDoctrine()->getManager()->persist($linky);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Persist the MeteoFrance Feed in DB and create dependent FeedData
     *
     * @param Feed $meteoFrance
     * @param Form $meteoFranceForm
     */
    private function persistMeteoFrance(Feed &$meteoFrance = NULL, Form $meteoFranceForm) {
        if(!$meteoFrance) {
            $meteoFrance = new Feed();
            $meteoFrance->setFeedType('METEO_FRANCE');
            $meteoFrance->setCreator('admin'); //@TODO Get yunohost user
            $meteoFrance->setPublic(TRUE); //@TODO Deal with yunohost users
        }
        $stations = $meteoFranceForm->get('station')->getConfig()->getOption('choices');
        $data = $meteoFranceForm->getData();
        $meteoFrance->setName($data['name']);
        $param = [
            'STATION_ID' => $data['station'],
            'CITY' => array_search($data['station'], $stations),
        ];
        $meteoFrance->setParam($param);
        $this->createDependentFeedData($meteoFrance);
        $this->getDoctrine()->getManager()->persist($meteoFrance);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * Create and persist Feed dependent FeedData according to it type
     *
     * @param Feed $feed
     */
    private function createDependentFeedData(Feed $feed) {
        $feedDataRepository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:FeedData');

        // We check, for this feed, if each dataFeedis already created,
        // and create it if not.
        foreach (Feed::FEED_TYPES[$feed->getFeedType()]['DATA_TYPE'] as $label => $data) {
            $feedData = $feedDataRepository->findOneByFeedAndDataType($feed, $label);
            if (!$feedData) {
                $feedData = new FeedData();
                $feedData->setDataType($label);
                $feedData->setFeed($feed);
                $this->getDoctrine()->getManager()->persist($feedData);
            }
        }
    }
}
