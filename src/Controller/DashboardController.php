<?php

namespace App\Controller;

use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    private $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }

    public function homepageAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/homepage.html.twig');
    }

    public function electricityAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/electricity.html.twig');
    }

    public function energyMeteoAction(Request $request)
    {
        if (!$this->canUserSeeAtLeastOnPlace()) {
            return $this->render('welcome.html.twig');
        }

        return $this->render('dashboards/energy_x_meteo.html.twig');
    }

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
