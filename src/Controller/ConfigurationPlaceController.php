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
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationPlaceController extends AbstractAppController
{
    /**
     * New Place form
     */
    public function placeNewAction(
        Request $request,
        int $userMaxPlaces,
        DataConnectServiceInterface $dataConnectService,
        JwtService $jwtService
    ): Response {
        $user = $this->getUser();
        \assert($user instanceof User);

        if (-1 != $userMaxPlaces && \count($user->getPlaces()) >= $userMaxPlaces) {
            throw new AccessDeniedHttpException(\sprintf(
                "Vous ne pouvez créer que %s adresse%s.",
                $userMaxPlaces,
                $userMaxPlaces > 1 ? 's' : ''
            ));
        }

        $enedisUrl = $dataConnectService
            ->getAuthorizeV1Service()
            ->getConsentPageUrl(
                'P12M',
                $jwtService->encode(['user' => $user->getId()])
            )
        ;

        return $this->render('configuration/place/new.html.twig', [
            'from_callback' => false,
            'enedis_url' => $enedisUrl,
        ]);
    }

    public function placeEditAction(
        Request $request,
        string $id,
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        DataConnectServiceInterface $dataConnectService,
        JwtService $jwtService
    ): Response {
        $user = $this->getUser();
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
                $place->addFeed($data['meteo']);
                $entityManager->persist($data['meteo']);
                $feedRepository->createDependentFeedData($data['meteo']);
                $entityManager->flush();
                $entityManager->persist($place);
                $entityManager->flush();

                $this->addFlash('success', "L'adresse a été correctement enregistrée.");

                return $this->redirectToRoute('config');
            }
        }

        $linkyFeed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);
        $enedisUrl = $dataConnectService
            ->getAuthorizeV1Service()
            ->getConsentPageUrl(
                'P12M',
                $jwtService->encode(['user' => $user->getId(), 'feed' => $linkyFeed->getId()])
            )
        ;

        return $this->render('configuration/place/edit.html.twig', [
            'form' => $form->createView(),
            'linky' => $linkyFeed,
            'enedis_url' => $enedisUrl,
        ]);
    }

    public function placeEnedisConsentCallbackAction(
        Request $request,
        DataConnectServiceInterface $dataConnectService,
        feedRepository $feedRepository,
        EntityManagerInterface $entityManager,
        JwtService $jwtService,
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

        try {
            $object = $jwtService->decode($state);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Erreur un décodant le jeton JWT', $e);
        }

        // Verifying we are the right user
        if ($object->user !== $user->getId()) {
            throw new AccessDeniedException();
        }

        $feed = null;
        if (isset($object->feed) && !$feed = $feedRepository->find($object->feed)) {
            throw new NotFoundHttpException();
        }
        \assert($feed instanceof Feed);

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

        if (!$feed) {
            // We are creating a new place
            $place = new Place();
            $place->setName((string) $address);
            $place->setUser($user);

            $entityManager->persist($place);
            $entityManager->flush();

            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_ELECTRICITY);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT);
            $feed->setPlace($place);
        }

        $feed->setName((string) $address);
        $feed->setSingleParam('TOKEN', $serializer->serialize($token, 'json'));
        $feed->setSingleParam('ADDRESS', $serializer->serialize($address, 'json'));

        $entityManager->persist($feed);
        $entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $feedRepository->createDependentFeedData($feed);
        $entityManager->flush();

        return $this->redirectToRoute('config.place.edit', ['id' => $feed->getPlace()->getId()]);
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
