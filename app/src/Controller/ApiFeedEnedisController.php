<?php

declare(strict_types=1);

namespace App\Controller;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use App\Entity\Feed;
use App\Entity\User;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\FeedDataProvider\ProxifiedEnedisDataConnectProvider;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ApiFeedEnedisController extends AbstractAppController
{
    private bool $useProxyForEnedis;

    private PendingActionService $actionService;
    private JwtService $jwtService;
    private EnedisDataConnectProvider $enedisDataConnectProvider;
    private ProxifiedEnedisDataConnectProvider $proxifiedEnedisDataConnectProvider;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        bool $useProxyForEnedis,
        PlaceRepository $placeRepository,
        PendingActionService $actionService,
        JwtService $jwtService,
        EnedisDataConnectProvider $enedisDataConnectProvider,
        ProxifiedEnedisDataConnectProvider $proxifiedEnedisDataConnectProvider
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->useProxyForEnedis = $useProxyForEnedis;

        $this->actionService = $actionService;
        $this->jwtService = $jwtService;
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
        $this->proxifiedEnedisDataConnectProvider = $proxifiedEnedisDataConnectProvider;
    }

    public function consent(string $placeId): JsonResponse
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createDataConnectCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        try {
            if ($this->useProxyForEnedis) {
                $enedisUrl = $this->proxifiedEnedisDataConnectProvider->getConsentUrl($state);
            } else {
                $enedisUrl = $this->enedisDataConnectProvider->getConsentUrl($state);
            }
        } catch (DataConnectException $e) {
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse($enedisUrl, 200);
    }

    public function consentCallback(Request $request): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$state = $request->get("state")) {
            return $this->dataValidationErrorResponse('state', "Un argument 'state' doit être fourni.");
        }

        $token = (string) $this->jwtService->decode($state);
        $pendingAction = $this->actionService->findActionByToken($user, $token);
        $place = $this->checkPlace($pendingAction->getSingleParam('place'));

        try {
            if ($this->useProxyForEnedis) {
                if (!$encodedPdl = $request->get("encodedPdl")) {
                    return $this->dataValidationErrorResponse('encodedPdl', "Un argument 'encodedPdl' doit être fourni.");
                }
                $this->proxifiedEnedisDataConnectProvider->handleConsentCallback($encodedPdl, $place);
            } else {
                if (!$usagePoints = $request->get('usage_point_id')) {
                    return $this->dataValidationErrorResponse('usage_point_id', "Un argument 'usage_point_id' doit être fourni.");
                }
                $usagePoints = \explode(',', $usagePoints);

                $this->enedisDataConnectProvider->handleConsentCallback(\reset($usagePoints), $place);
            }
        } catch (DataConnectException $e) {
            $this->logger->error(
                '[ENEDIS] - Erreur lors du retour de consentement : ' . $e->getMessage(),
                ['exception' => $e]
            );
            // Sur une erreur au retour d'enedis data-connect, sur une erreur
            // on renvoit sur une page d'erreur du front
            return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/error/' . $place->getId()]);
        }

        $this->actionService->delete($pendingAction);

        return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/enedis/' . $place->getId()]);
    }

    public function consentCheck(string $placeId): JsonResponse
    {
        $place = $this->checkPlace($placeId);

        $enedisFeed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);
        if (!$$enedisFeed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY)) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Linky n'est associé à cette adresse.");
        }

        switch($enedisFeed->getFeedDataProviderType()) {
            case Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT:
                $address = $this->enedisDataConnectProvider->consentCheck($enedisFeed);
                break;
            case Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED:
                $address = $this->proxifiedEnedisDataConnectProvider->consentCheck($enedisFeed);
                break;
            default:
                return $this->dataValidationErrorResponse('feed', "Aucun compteur Linky n'est associé à cette adresse.");
        }

        if (!$address = $this->enedisDataConnectProvider->consentCheck($enedisFeed)) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($address), 200);
    }
}
