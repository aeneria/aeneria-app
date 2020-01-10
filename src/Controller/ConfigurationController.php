<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\GenericFeedDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends AbstractController
{
    private $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }

    /**
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {
        // We get a Place if it already exists.
        $places = $this->placeRepository->findByUser($this->getUser());

        return $this->render('configuration/configuration.html.twig', [
            'places' => $places
        ]);
    }

    /**
     * @Route("/configuration/place/add", name="config.place.add")
     */
    public function placeAddAction(Request $request, EntityManagerInterface $entityManager)
    {
        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, null, [
            'data_class' => null,
            'user' => $this->getUser(),
        ]);

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($entityManager, $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'La nouvelle adresse a bien été enregistrée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/update", name="config.place.update")
     */
    public function placeUpdateAction(Request $request, string $id, EntityManagerInterface $entityManager)
    {
        $place = $this->checkPlace($id);

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, $place, [
                'data_class' => null,
                'user' => $this->getUser(),
            ])
        ;

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($entityManager, $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'Votre configuration a bien été enregistrée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/delete", name="config.place.delete")
     */
    public function placeDeleteAction(Request $request, string $id)
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
                'attr' => ['class' => 'btn btn-danger'],
                'label' => "Supprimer l'adresse et TOUTES ses données",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $place = $this->placeRepository->purge($place);
                $this->addFlash('success', 'L\'adresse a bien été supprimée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Supprimer une adresse',
            'form' => $form->createView(),
            'cancel' => 'config'
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/fetch", name="config.place.fetch")
     */
    public function placeFetchAction(Request $request, GenericFeedDataProvider $feedDataProvider, FormFactoryInterface $formFactory, string $id)
    {
        $place = $this->checkPlace($id);

        $forms = [];
        $feeds = [];

        foreach ($place->getFeeds() as $feed) {
            $feedId = $feed->getId();

            $feeds[$feedId] = $feed;
            $forms[$feedId] = $formFactory
                ->createNamedBuilder($feedId)
                ->add('start_date_' . $feedId, Form\TextType::class, [
                    'label' => false,
                    'help' => $feed->getFeedType() === 'METEO' ? "Attention, les données météorologiques ne sont plus accessibles après 2 semaines." : '',
                    'attr' => ['class' => 'simple-datepicker'],
                    'required' => true,
                ])
                ->add('end_date_' . $feedId, Form\TextType::class, [
                    'label' => false,
                    'attr' => ['class' => 'simple-datepicker'],
                    'required' => true,
                ])
                ->add('force_' . $feedId, Form\CheckboxType::class, [
                    'label' => 'Forcer',
                    'required' => false,
                ])
                ->add('submit_' . $feedId, Form\SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-warning',
                        'title' => 'Recharger',
                    ],
                    'label' => '',
                ])
                ->getForm()
                ->handleRequest($request)
            ;
        }

        if('POST' === $request->getMethod()) {
            foreach ($forms as $feedId => $form) {
                if ($request->request->has($feedId) && $form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();

                    $startDate = \DateTime::createFromFormat('d/m/Y', $data['start_date_' . $feedId]);
                    $endDate = \DateTime::createFromFormat('d/m/Y', $data['end_date_' . $feedId]);

                    $feedDataProvider->fetchDataBetween($startDate, $endDate, [$feeds[$feedId]], $data['force_' . $feedId]);

                    $message = \sprintf(
                        'Les données %s ont été correctement rechargées entre le %s et le %s.',
                        \ucfirst($feeds[$feedId]->getName()),
                        $data['start_date_' . $feedId],
                        $data['end_date_' . $feedId]
                    );

                    $this->addFlash('success', $message);
                }
            }
        }

        $views = [];
        foreach ($forms as $key => $form) {
            $views[$key] = $form->createView();
        }

        return $this->render('configuration/place_fetch.html.twig', [
            'place' => $place,
            'feeds' => $feeds,
            'forms' => $views,
            'cancel' => 'config'
        ]);
    }

    private function checkPlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        if (!$this->getUser()->canEdit($place)) {
            throw new AccessDeniedException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
        }

        return $place;
    }
}
