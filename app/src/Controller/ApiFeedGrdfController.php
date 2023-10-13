<?php

declare(strict_types=1);

namespace App\Controller;

use App\GrdfAdict\Exception\GrdfAdictException;
use App\Entity\Feed;
use App\Entity\User;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use App\Services\FeedDataProvider\ProxifiedGrdfAdictProvider;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ApiFeedGrdfController extends AbstractAppController
{
    /** @var bool */
    private $useProxyForGrdf;

    /** @var PendingActionService */
    private $actionService;
    /** @var JwtService */
    private $jwtService;
    /** @var GrdfAdictProvider */
    private $grdfAdictProvider;
    /** @var ProxifiedGrdfAdictProvider */
    private $proxifiedGrdfAdictProvider;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        bool $useProxyForGrdf,
        PlaceRepository $placeRepository,
        PendingActionService $actionService,
        JwtService $jwtService,
        GrdfAdictProvider $grdfAdictProvider,
        ProxifiedGrdfAdictProvider $proxifiedGrdfAdictProvider
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->useProxyForGrdf = $useProxyForGrdf;

        $this->actionService = $actionService;
        $this->jwtService = $jwtService;
        $this->grdfAdictProvider = $grdfAdictProvider;
        $this->proxifiedGrdfAdictProvider = $proxifiedGrdfAdictProvider;
    }

    public function consent(string $placeId): JsonResponse
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createGrdfAdictCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        try {
            if ($this->useProxyForGrdf) {
                $grdfUrl = $this->proxifiedGrdfAdictProvider->getConsentUrl($state);
            } else {
                $grdfUrl = $this->grdfAdictProvider->getConsentUrl($state);
            }
        } catch (GrdfAdictException $e) {
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse($grdfUrl, 200);
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
            if ($this->useProxyForGrdf) {
                if (!$encodedPce = $request->get("encodedPce")) {
                    return $this->dataValidationErrorResponse('encodedPce', "Un argument 'encodedPce' doit être fourni.");
                }

                $this->proxifiedGrdfAdictProvider->handleConsentCallback($encodedPce, $place);
            } else {
                if (!$code = $request->get("code")) {
                    return $this->dataValidationErrorResponse('code', "Un argument 'code' doit être fourni.");
                }

                $this->grdfAdictProvider->handleConsentCallback($code, $place);
            }
        } catch (GrdfAdictException $e) {
            return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/error/' . $place->getId()]);
        }

        $this->actionService->delete($pendingAction);

        return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/grdf/' . $place->getId()]);
    }

    public function consentCheck(string $placeId): JsonResponse
    {
        $place = $this->checkPlace($placeId);

        if (!$grdfFeed = $place->getFeed(Feed::FEED_TYPE_GAZ)) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Gazpar n'est associé à cette adresse.");
        }

        $info = null;
        switch($grdfFeed->getFeedDataProviderType()) {
            case Feed::FEED_DATA_PROVIDER_GRDF_ADICT:
                $info = $this->grdfAdictProvider->consentCheck($grdfFeed);
                break;
            case Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED:
                $info = $this->proxifiedGrdfAdictProvider->consentCheck($grdfFeed);
                break;
            default:
                return $this->dataValidationErrorResponse('feed', "Aucun compteur Gazpar n'est associé à cette adresse.");
        }

        if (!$info) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($info), 200);
    }
}
