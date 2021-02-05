<?php

namespace App\Controller;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use Aeneria\EnedisDataConnectApi\Service\DataConnectServiceInterface;
use App\Entity\Feed;
use App\Entity\Place;
use App\Entity\User;
use App\Form\MeteoFranceFeedType;
use App\Form\PlaceType;
use App\Repository\FeedRepository;
use App\Services\JwtService;
use App\Services\PendingActionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationPlaceController extends AbstractAppController
{
    /**
     * New Place form
     */
    public function placeNewAction(int $userMaxPlaces): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        if ((-1 != $userMaxPlaces && \count($user->getPlaces()) >= $userMaxPlaces) || $this->isDemoMode) {
            throw new AccessDeniedHttpException("Vous ne pouvez plus créer d'adresse.");
        }

        return $this->render('configuration/place/new.html.twig', [
            'from_callback' => false,
        ]);
    }

    public function placeEditAction(
        Request $request,
        string $id,
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository
    ): Response {
        $place = $this->checkPlace($id);

        $form = $this
            ->createFormBuilder([
                'place' => $place,
                'meteo' => $place->getFeed(Feed::FEED_TYPE_METEO),
            ])
            ->add('place', PlaceType::class, [
                'data_class' => null,
                'user' => $this->getUser(),
                'user_can_share_place' => $userCanSharePlace,
                'place_can_be_public' => $placeCanBePublic,
            ])
            ->add('meteo', MeteoFranceFeedType::class, ['data_class' => null])
            ->add('cancel', Form\SubmitType::class, [
                'label' => 'Annuler',
                'attr' => ['class' => 'btn btn-primary float-left', 'formnovalidate' => ''],
            ])
            ->add('save', Form\SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ('POST' === $request->getMethod()) {
            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('config');
            }

            if ($form->isValid()) {
                $data = $form->getData();
                $place = $data['place'];
                \assert($place instanceof Place);

                // Deal with MeteoFrance Feed
                if ($oldMeteoFranceFeed = $place->getFeed(Feed::FEED_TYPE_METEO)) {
                    $place->removeFeed($oldMeteoFranceFeed);
                }
                $meteoFeed = $feedRepository->getOrCreateMeteoFranceFeed($data['meteo']);
                $place->addFeed($meteoFeed);
                $entityManager->persist($meteoFeed);

                $entityManager->persist($place);
                $entityManager->flush();

                $this->addFlash('success', "L'adresse a été correctement enregistrée.");

                return $this->redirectToRoute('config');
            }
        }

        $linkyFeed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);

        return $this->render('configuration/place/edit.html.twig', [
            'form' => $form->createView(),
            'linky' => $linkyFeed,
            'place' => $place,
        ]);
    }

    public function placeEnedisConsentAction(
        RouterInterface $router,
        string $id = null,
        int $userMaxPlaces,
        DataConnectServiceInterface $dataConnectService,
        PendingActionService $actionService,
        JwtService $jwtService
    ): RedirectResponse {
        $user = $this->getUser();

        $place = null;
        if ($id) {
            $place = $this->checkPlace($id);
        } elseif (-1 != $userMaxPlaces && \count($user->getPlaces()) >= $userMaxPlaces && !$this->isDemoMode) {
            throw new AccessDeniedHttpException(\sprintf(
                "Vous ne pouvez créer que %s adresse%s.",
                $userMaxPlaces,
                $userMaxPlaces > 1 ? 's' : ''
            ));
        }

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $state = $jwtService->encode($action->getToken());

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
            $router->generate('config.place.enedis_consent_callback', [], RouterInterface::ABSOLUTE_URL)
        );

        return $this->redirect($enedisUrl);
    }

    public function placeEnedisConsentCallbackAction(
        Request $request,
        DataConnectServiceInterface $dataConnectService,
        JwtService $jwtService,
        feedRepository $feedRepository,
        EntityManagerInterface $entityManager,
        PendingActionService $actionService,
        SerializerInterface $serializer
    ): Response {
        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$code = $request->get('code')) {
            throw new BadRequestHttpException();
        }
        if (!$state = $request->get("state")) {
            throw new BadRequestHttpException();
        }

        $token = (string) $jwtService->decode($state);
        $pendingAction = $actionService->findActionByToken($user, $token);

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
            $this->addFlash('danger', "Une erreur est survenue, réessayez plus tard.");

            return $this->redirectToRoute('config');
        }

        if ($pendingAction->existParam('place')) {
            $place = $this->checkPlace($pendingAction->getSingleParam('place'));
        } else {
            // We are creating a new place
            $place = new Place();
            $place->setName((string) $address);
            $place->setUser($user);

            $entityManager->persist($place);
            $entityManager->flush();
        }
        \assert($place instanceof Place);

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_ELECTRICITY);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT);
            $place->addFeed($feed);
        }

        $feed->setName((string) $address);
        $feed->setSingleParam('TOKEN', $serializer->serialize($token, 'json'));
        $feed->setSingleParam('ADDRESS', $serializer->serialize($address, 'json'));

        $entityManager->persist($feed);
        $entityManager->persist($place);
        $entityManager->flush();

        $this->addFlash('success', "Le partage de données a été correctement activé.");

        // Ensure all dependant FeedData are already existing
        $feedRepository->createDependentFeedData($feed);
        $entityManager->flush();

        $actionService->delete($pendingAction);

        return $this->redirectToRoute('config.place.edit', ['id' => $place->getId()]);
    }

    /**
     * Delete Place form view
     */
    public function placeDeleteAction(Request $request, string $id): Response
    {
        $place = $this->checkPlace($id);

        $form = $this
            ->createFormBuilder()
            ->add('are_you_sure', Form\CheckboxType::class, [
                'label' => "Veuillez cocher cette case si vous êtes sûr de vouloir supprimer cette adresse",
                'help' => "Attention, cette action entrainera la suppression de TOUTES les données associées à cette adresse.",
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger float-right'],
                'label' => "Supprimer l'adresse et TOUTES ses données",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $place = $this->placeRepository->purge($place);
                $this->addFlash('success', 'L\'adresse a bien été supprimée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Supprimer une adresse',
            'form' => $form->createView(),
            'cancel' => 'config',
        ]);
    }
}
