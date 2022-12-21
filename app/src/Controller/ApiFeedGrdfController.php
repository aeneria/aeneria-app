<?php

namespace App\Controller;

use Aeneria\GrdfAdictApi\Exception\GrdfAdictConsentException;
use App\Entity\Feed;
use App\Entity\User;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ApiFeedGrdfController extends AbstractAppController
{
    /** @var PendingActionService */
    private $actionService;
    /** @var JwtService */
    private $jwtService;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var FeedRepository */
    private $feedRepository;
    /** @var GrdfAdictProvider */
    private $grdfAdictProvider;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        PendingActionService $actionService,
        JwtService $jwtService,
        EntityManagerInterface $entityManager,
        GrdfAdictProvider $grdfAdictProvider
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->actionService = $actionService;
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        $this->grdfAdictProvider = $grdfAdictProvider;
    }

    public function consent(string $placeId): JsonResponse {
        $user = $this->getUser();
        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createGrdfAdictCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        $grdfUrl = $this->grdfAdictProvider->getConsentUrl($state);

        return new JsonResponse($grdfUrl, 200);
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
            $info = $this->grdfAdictProvider->consentCheckFromCode($code);
        } catch (GrdfAdictConsentException $e) {
            // Sur une erreur au retour d'grdf, sur une erreur
            // on renvoit sur une page d'erreur du front
            return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/error/' . $place->getId()]);
        }

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_GAZ)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_GAZ);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_GRDF_ADICT);
            $place->addFeed($feed);
        }

        $feed->setName($info ? (string) $info : '');
        $feed->setFetchError(0);
        $feed->setSingleParam('PCE', $info->pce);
        $feed->setSingleParam('INFO', $serializer->serialize($info, 'json'));

        $this->entityManager->persist($feed);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $this->feedRepository->createDependentFeedData($feed);
        $this->entityManager->flush();

        $this->actionService->delete($pendingAction);

        return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/grdf/' . $place->getId()]);
    }

    public function consentCheck(string $placeId): JsonResponse
    {
        $place = $this->checkPlace($placeId);

        $grdfFeed = $place->getFeed(Feed::FEED_TYPE_GAZ);
        if (!$grdfFeed or Feed::FEED_DATA_PROVIDER_GRDF_ADICT !== $grdfFeed->getFeedDataProviderType()) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Gazpar n'est associé à cette adresse.");
        }

        if (!$info = $this->grdfAdictProvider->consentCheck($grdfFeed)) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($info), 200);
    }
}
