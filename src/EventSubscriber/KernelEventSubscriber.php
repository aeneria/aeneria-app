<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\DataValueRepository;
use App\Repository\PlaceRepository;
use App\Services\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * @codeCoverageIgnore
 */
final class KernelEventSubscriber implements EventSubscriberInterface
{
    /** @var Environment */
    private $twig;
    /** @var PlaceRepository */
    private $placeRepository;
    /** @var DataValueRepository */
    private $dataValueRepository;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var NotificationService */
    private $notificationService;

    /**
     * Default constructor
     */
    public function __construct(
        Environment $twig,
        PlaceRepository $placeRepository,
        DataValueRepository $dataValueRepository,
        TokenStorageInterface $tokenStorage,
        NotificationService $notificationService
    ) {
        $this->twig = $twig;
        $this->placeRepository = $placeRepository;
        $this->dataValueRepository = $dataValueRepository;
        $this->tokenStorage = $tokenStorage;
        $this->notificationService = $notificationService;
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
    public function onController(ControllerEvent $event)
    {
        if ($token = $this->tokenStorage->getToken()) {
            $places = [];
            $notifications = [];
            if (($user = $token->getUser()) instanceof User) {
                // Add places global var
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

                // Add notifications global var
                $notifications = $this->notificationService->getAndDeleteNotificationFor($user);

                $this->twig->addGlobal('user', $user);
            }
            $this->twig->addGlobal('places', $places);
            $this->twig->addGlobal('notifications', $notifications);
        }
    }
}
