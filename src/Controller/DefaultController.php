<?php

namespace App\Controller;

use App\Repository\DataValueRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    private $dataValueRepository;
    private $placeRepository;

    public function __construct(PlaceRepository $placeRepository, DataValueRepository $dataValueRepository)
    {
        $this->dataValueRepository = $dataValueRepository;
        $this->placeRepository = $placeRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        if (\count($this->getUser()->getPlaces()) == 0) {
            return $this->redirectToRoute('welcome');
        }

        return $this->render('dashboards/homepage.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/electricity", name="general")
     */
    public function electricityAction(Request $request)
    {
        if (\count($this->getUser()->getPlaces()) == 0) {
            return $this->redirectToRoute('welcome');
        }

        return $this->render('dashboards/electricity.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/energy_x_meteo", name="energy_x_meteo")
     */
    public function energymeteoAction(Request $request)
    {
        if (\count($this->getUser()->getPlaces()) == 0) {
            return $this->redirectToRoute('welcome');
        }

        return $this->render('dashboards/energy_x_meteo.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/meteo", name="meteo")
     */
    public function meteoAction(Request $request)
    {
        if (\count($this->getUser()->getPlaces()) == 0) {
            return $this->redirectToRoute('welcome');
        }

        return $this->render('dashboards/meteo.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/welcome", name="welcome")
     */
    public function welcomeAction(Request $request)
    {
        return $this->render('welcome.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
}
