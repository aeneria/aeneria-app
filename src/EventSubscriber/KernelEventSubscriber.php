<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Repository\DataValueRepository;
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
    private $dataValueRepository;
    private $tokenStorage;

    /**
     * Default constructor
     */
    public function __construct(Environment $twig, DataValueRepository $dataValueRepository, TokenStorageInterface $tokenStorage)
    {
        $this->twig = $twig;
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
            if ($user = $token->getUser()) {
                $places = [];
                foreach ($user->getPlaces() as $place) {
                    $period = $this->dataValueRepository->getPeriodDataAmplitude($place);
                    $places[$place->getId()] = [
                        'name' => $place->getName(),
                        'start' => $period[1],
                        'end' => $period[2],
                    ];
                }
                $this->twig->addGlobal('places', $places);
            }
        }
    }
}
