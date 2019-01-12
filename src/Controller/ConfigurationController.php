<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Feed;
use App\Form\ConfigurationType;
use App\Entity\FeedData;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {

        /** @var \Symfony\Component\Form\Form $configForm */
        list($configForm, $linky, $meteoFrance) = $this->prepareConfigForm();

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                $this->persistConfig($linky, $meteoFrance, $configForm);
            }
        }

        return $this->render('pages/configuration.html.twig', array(
            'form_config' => $configForm->createView(),
        ));
    }

    /**
     * Prepare the Config form & get existing Linky & MeteoFrance feed.
     *
     * @return [\Symfony\Component\Form\FormBuilder, \App\Entity\Feed, \App\Entity\Feed]
     */
    private function prepareConfigForm() {
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

        // We set defaultValue if there's already a linky feed.
        $defaultValue= [];
        if ($linky) {
            $defaultValue['name'] = $linky->getName();
            $param = $linky->getParam();
            foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $paramName => $label) {
                $defaultValue[strtolower($paramName)] = $param[$paramName];
            }
        }
        if ($meteoFrance) {
            $param = $meteoFrance->getParam();
            $defaultValue['station'] = (int)$param['STATION_ID'];
        }

        /** @var \Symfony\Component\Form\FormBuilder $linkyForm */
        $configForm = $this
            ->get('form.factory')
            ->createNamedBuilder('form_config', ConfigurationType::class, $defaultValue)
            ->getForm();

        return [$configForm, $linky, $meteoFrance];
    }



    /**
     * Persist the Liny & MeteoFrance Feed in DB and create dependent FeedData
     *
     * @param Feed $linky
     * @param Feed $meteoFrance
     * @param Form $vonfigForm
     */
    private function persistConfig(Feed &$linky = NULL, Feed &$meteoFrance, Form $configForm) {
        $data = $configForm->getData();

        // Create/update Linky.
        if(!$linky) {
            $linky = new Feed();
            $linky->setFeedType('LINKY');
            $linky->setCreator('admin'); //@TODO Get yunohost user
            $linky->setPublic(TRUE); //@TODO Deal with yunohost users
        }

        $linky->setName('linky');
        $param = [];
        foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
            $param[$name] = $data[strtolower($name)];
        }
        $linky->setParam($param);
        $this->createDependentFeedData($linky);
        $this->getDoctrine()->getManager()->persist($linky);

        // Create/update MeteoFrance.
        if(!$meteoFrance) {
            $meteoFrance = new Feed();
            $meteoFrance->setFeedType('METEO_FRANCE');
            $meteoFrance->setCreator('admin'); //@TODO Get yunohost user
            $meteoFrance->setPublic(TRUE); //@TODO Deal with yunohost users
        }
        $stations = $configForm->get('station')->getConfig()->getOption('choices');
        $meteoFrance->setName('meteo');
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
            ->getRepository('App:FeedData');

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
