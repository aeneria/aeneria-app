<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\DataValueRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * @codeCoverageIgnore
 */
final class KernelEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $placeRepository;
    private $dataValueRepository;
    private $tokenStorage;

    /**
     * Default constructor
     */
    public function __construct(Environment $twig, PlaceRepository $placeRepository, DataValueRepository $dataValueRepository, TokenStorageInterface $tokenStorage)
    {
        $this->twig = $twig;
        $this->placeRepository = $placeRepository;
        $this->dataValueRepository = $dataValueRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdo}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    /**
     * Set twig global variables
     */
    public function onController(FilterControllerEvent $event)
    {
        if ($token = $this->tokenStorage->getToken()) {
            $places = [];
            if (($user = $token->getUser()) instanceof User) {
                if ($allowedPlaces = $this->placeRepository->getAllowedPlaces($user)) {
                    foreach ($allowedPlaces as $place) {
                        $period = $this->dataValueRepository->getPeriodDataAmplitude($place);
                        $places[$place->getId()] = [
                            'name' => $place->getName(),
                            'icon' => $place->getIcon() ? $place->getIcon() : 'map-marker-alt',
                            'start' => $period[1],
                            'end' => $period[2],
                        ];
                    }
                }
            }
            $this->twig->addGlobal('places', $places);
        }
    }
}
