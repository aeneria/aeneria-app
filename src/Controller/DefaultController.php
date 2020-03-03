<?php

namespace App\Controller;

use App\Repository\DataValueRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    private $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/homepage.html.twig');
    }

    /**
     * @Route("/electricity", name="general")
     */
    public function electricityAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/electricity.html.twig');
    }

    /**
     * @Route("/energy_x_meteo", name="energy_x_meteo")
     */
    public function energymeteoAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/energy_x_meteo.html.twig');
    }

    /**
     * @Route("/meteo", name="meteo")
     */
    public function meteoAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/meteo.html.twig');
    }

    private function canUserSeeAtLeastOnPlace(): bool
    {
        return \count($this->placeRepository->getAllowedPlaces($this->getUser())) > 0;
    }
}
