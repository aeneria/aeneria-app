<?php

namespace App\Controller;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use App\Entity\Feed;
use App\Entity\User;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ApiFeedEnedisController extends AbstractAppController
{
    /** @var PendingActionService */
    private $actionService;
    /** @var JwtService */
    private $jwtService;
    /** @var EnedisDataConnectProvider */
    private $enedisDataConnectProvider;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        PlaceRepository $placeRepository,
        PendingActionService $actionService,
        JwtService $jwtService,
        EnedisDataConnectProvider $enedisDataConnectProvider
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->actionService = $actionService;
        $this->jwtService = $jwtService;
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
    }

    public function consent(string $placeId): JsonResponse
    {
        $user = $this->getUser();
        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createDataConnectCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        $enedisUrl = $this->enedisDataConnectProvider->getConsentUrl($state);

        return new JsonResponse($enedisUrl, 200);
    }

    public function consentCallback(Request $request, SerializerInterface $serializer): RedirectResponse
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$code = $request->get('code')) {
            return $this->dataValidationErrorResponse('code', "Un argument 'code' doit être fourni.");
        }
        if (!$state = $request->get("state")) {
            return $this->dataValidationErrorResponse('state', "Un argument 'state' doit être fourni.");
        }

        $token = (string) $this->jwtService->decode($state);
        $pendingAction = $this->actionService->findActionByToken($user, $token);
        $place = $this->checkPlace($pendingAction->getSingleParam('place'));

        try {
            $this->enedisDataConnectProvider->handleConsentCallback($code, $place);
        } catch (DataConnectException $e) {
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
        if (!$enedisFeed or Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $enedisFeed->getFeedDataProviderType()) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Linky n'est associé à cette adresse.");
        }

        if (!$address = $this->enedisDataConnectProvider->consentCheck($enedisFeed)) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($address), 200);
    }
}
