<?php

namespace App\Controller;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use Aeneria\EnedisDataConnectApi\Service\DataConnectServiceInterface;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictConsentException;
use Aeneria\GrdfAdictApi\Service\GrdfAdictServiceInterface;
use App\Entity\Feed;
use App\Entity\User;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiFeedController extends AbstractAppController
{
    /** @var RouterInterface */
    private $router;
    /** @var PendingActionService */
    private $actionService;
    /** @var JwtService */
    private $jwtService;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var FeedRepository */
    private $feedRepository;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        RouterInterface $router,
        PendingActionService $actionService,
        JwtService $jwtService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->router = $router;
        $this->actionService = $actionService;
        $this->jwtService = $jwtService;
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
    }

    public function getMeteoStationList(MeteoFranceDataProvider $meteoFranceDataProvider): JsonResponse
    {
        return new JsonResponse($meteoFranceDataProvider->getAvailableStations(), 200);
    }

    public function meteoUpdate(
        string $placeId,
        Request $request,
        MeteoFranceDataProvider $meteoFranceDataProvider
    ): JsonResponse {
        $place = $this->checkPlace($placeId);
        $data = \json_decode($request->getContent());

        if (!$data->meteo) {
            return $this->dataValidationErrorResponse('meteo', "Vous devez fournir un id de station meteo 'meteo'.");
        }
        if(!$station = $meteoFranceDataProvider->findStationByKey((int) $data->meteo)) {
            return $this->dataValidationErrorResponse('meteo', \sprintf("La station météo '%s' n'existe pas.", $data->meteo));
        }
        $meteoFeed = $this->feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => $station->key,
            'CITY' => $station->label,
        ]);

        if ($oldMeteoFranceFeed = $place->getFeed(Feed::FEED_TYPE_METEO)) {
            $place->removeFeed($oldMeteoFranceFeed);
        }
        $place->addFeed($meteoFeed);

        $this->entityManager->persist($meteoFeed);

        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return new JsonResponse($place->jsonSerialize(), 200);
    }

    public function enedisConsent(
        string $placeId,
        DataConnectServiceInterface $dataConnectService
    ): JsonResponse {
        $user = $this->getUser();
        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createDataConnectCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        $enedisUrl = $dataConnectService
            ->getAuthorizeV1Service()
            ->getConsentPageUrl(
                'P12M',
                $state
            )
        ;

        // Adding callback url for aeneria proxy
        $enedisUrl .= '&callback=';
        $enedisUrl .= \urlencode(
            $this->router->generate('api.feed.enedis.consent.callback', [], RouterInterface::ABSOLUTE_URL)
        );

        return new JsonResponse($enedisUrl, 200);
    }

    public function enedisConsentCheck(
        string $placeId,
        EnedisDataConnectProvider $enedisDataConnectProvider
    ): JsonResponse {
        $place = $this->checkPlace($placeId);

        $enedisFeed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);
        if (!$enedisFeed or Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $enedisFeed->getFeedDataProviderType()) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Linky n'est associé à cette adresse.");
        }

        if (!$address = $enedisDataConnectProvider->consentCheck($enedisFeed)) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($address), 200);
    }

    public function enedisConsentCallback(
        Request $request,
        DataConnectServiceInterface $dataConnectService,
        SerializerInterface $serializer
    ): RedirectResponse {
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
            $token = $dataConnectService
                ->getAuthorizeV1Service()
                ->requestTokenFromCode($code)
            ;

            $address = $dataConnectService
                ->getCustomersService()
                ->requestUsagePointAdresse(
                    $token->getAccessToken(),
                    $token->getUsagePointsId()
                )
            ;
        } catch (DataConnectException $e) {
            // Sur une erreur au retour d'enedis data-connect, sur une erreur
            // on renvoit sur une page d'erreur du front
            return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/error/' . $place->getId()]);
        }

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_ELECTRICITY);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT);
            $place->addFeed($feed);
        }

        $feed->setName((string) $address);
        $feed->setSingleParam('TOKEN', $serializer->serialize($token, 'json'));
        $feed->setSingleParam('ADDRESS', $serializer->serialize($address, 'json'));
        $feed->setSingleParam('FETCH_ERROR', 0);

        $this->entityManager->persist($feed);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $this->feedRepository->createDependentFeedData($feed);
        $this->entityManager->flush();

        $this->actionService->delete($pendingAction);

        return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/enedis/' . $place->getId()]);
    }

    public function grdfConsent(
        string $placeId,
        GrdfAdictServiceInterface $grdfAdictService
    ): JsonResponse {
        $user = $this->getUser();
        $place = $this->checkPlace($placeId);

        $action = $this->actionService->createGrdfAdictCallbackAction($user, $place);
        $state = $this->jwtService->encode($action->getToken());

        $grdfUrl = $grdfAdictService
            ->getAuthentificationService()
            ->getConsentPageUrl(
                $state,
                'aeneria'
            )
        ;

        // Adding callback url for aeneria proxy
        $grdfUrl .= '&callback=';
        $grdfUrl .= \urlencode(
            $this->router->generate('api.feed.grdf.consent.callback', [], RouterInterface::ABSOLUTE_URL)
        );

        return new JsonResponse($grdfUrl, 200);
    }

    public function grdfConsentCheck(
        string $placeId,
        GrdfAdictProvider $grdfAdictProvider
    ): JsonResponse {
        $place = $this->checkPlace($placeId);

        $grdfFeed = $place->getFeed(Feed::FEED_TYPE_GAZ);
        if (!$grdfFeed or Feed::FEED_DATA_PROVIDER_GRDF_ADICT !== $grdfFeed->getFeedDataProviderType()) {
            return $this->dataValidationErrorResponse('feed', "Aucun compteur Gazpar n'est associé à cette adresse.");
        }

        if (!$info = $grdfAdictProvider->consentCheck($grdfFeed)) {
            return $this->dataValidationErrorResponse('feed', "Il y a eut une erreur au moment de validé le consentement.");
        }

        return new JsonResponse(\json_encode($info), 200);
    }

    public function grdfConsentCallback(
        Request $request,
        GrdfAdictServiceInterface $grdfAdictService,
        SerializerInterface $serializer
    ): RedirectResponse {
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
            $consentement = $grdfAdictService
                ->getAuthentificationService()
                ->requestConsentementDetail($code)
            ;

            $authorisationToken = $grdfAdictService
                ->getAuthentificationService()
                ->requestAuthorizationToken()
            ;

            $info = null;
            try {
                $info = $grdfAdictService
                    ->getContratService()
                    ->requestInfoTechnique(
                        $authorisationToken->getAccessToken(),
                        $consentement->getPce()
                    )
                ;
            } catch (GrdfAdictConsentException $e) {
                // @todo : pourquoi j'avais fait ça ? à revoir
            }
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
        $feed->setSingleParam('FETCH_ERROR', 0);
        $feed->setSingleParam('PCE', $consentement->getPce());
        if ($info) {
            $feed->setSingleParam('INFO', $serializer->serialize($info, 'json'));
        }

        $this->entityManager->persist($feed);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $this->feedRepository->createDependentFeedData($feed);
        $this->entityManager->flush();

        $this->actionService->delete($pendingAction);

        return $this->redirectToRoute('app.home.trailing', ['slug' => 'mon-compte/callback/grdf/' . $place->getId()]);
    }
}