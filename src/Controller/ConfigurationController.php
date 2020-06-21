<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PlaceType;
use App\Form\UpdateAccountType;
use App\Services\DataExporter;
use App\Services\FeedDataProvider\GenericFeedDataProvider;
use App\Validator\Constraints\UniqueUsername;
use App\Validator\Constraints\UpdatePassword;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ConfigurationController extends AbstractAppController
{
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
    public function placeAddAction(int $userMaxPlaces, bool $userCanSharePlace, bool $placeCanBePublic, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        if($userMaxPlaces != -1 && \count($user->getPlaces()) >= $userMaxPlaces) {
            throw new AccessDeniedHttpException(\sprintf(
                "Vous ne pouvez créer que %s adresse%s.",
                $userMaxPlaces,
                $userMaxPlaces>1 ? 's' : ''
            ));
        }

        $configForm = $this->createForm(PlaceType::class, null, [
            'data_class' => null,
            'user' => $this->getUser(),
            'user_can_share_place' => $userCanSharePlace,
            'place_can_be_public' => $placeCanBePublic,
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
    public function placeUpdateAction(bool $userCanSharePlace, bool $placeCanBePublic, Request $request, string $id, EntityManagerInterface $entityManager)
    {
        $place = $this->checkPlace($id);

        $configForm = $this->createForm(PlaceType::class, $place, [
                'data_class' => null,
                'user' => $this->getUser(),
                'user_can_share_place' => $userCanSharePlace,
                'place_can_be_public' => $placeCanBePublic,
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
    public function placeFetchAction(bool $userCanFetch, Request $request, GenericFeedDataProvider $feedDataProvider, FormFactoryInterface $formFactory, string $id)
    {
        if (!$userCanFetch) {
            throw new NotFoundHttpException();
        }

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
                    'help' => $feed->getFeedType() === 'METEO' ?
                        "Attention, les données météorologiques ne sont plus accessibles après 2 semaines."
                        :
                        "Le processus de rechargement des données pouvant être long, il n'est possible de recharger que par lot de 2 semaines",
                    'attr' => ['class' => 'simple-datepicker'],
                    'required' => true,
                ])
                ->add('end_date_' . $feedId, Form\TextType::class, [
                    'label' => false,
                    'attr' => ['class' => 'simple-datepicker'],
                    'required' => true,
                    'constraints' => [new Callback([
                        'callback' => static function ($value, ExecutionContextInterface $context) use ($feedId) {
                            $data = $context->getRoot()->getData();
                            $startDate = \DateTime::createFromFormat('d/m/Y', $data['start_date_' . $feedId]);
                            $endDate = \DateTime::createFromFormat('d/m/Y', $data['end_date_' . $feedId])->sub(new \DateInterval('P14D'));
                            if ($startDate < $endDate) {
                                $context
                                    ->buildViolation("Vous devez sélectionner une période de moins de 2 semaines.")
                                    ->addViolation()
                                ;
                            }
                        }
                    ])]
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

                    $startDate = \DateTimeImmutable::createFromFormat('d/m/Y', $data['start_date_' . $feedId]);
                    $endDate = \DateTimeImmutable::createFromFormat('d/m/Y', $data['end_date_' . $feedId]);

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

    /**
     * @Route("/configuration/place/{id}/export", name="config.place.export")
     */
    public function placeExportAction(bool $userCanExport, Request $request, DataExporter $dataExporter, string $id)
    {
        if (!$userCanExport) {
            throw new NotFoundHttpException();
        }

        $place = $this->checkPlace($id);

        $form = $this
            ->createFormBuilder()
            ->add('start_date', Form\TextType::class, [
                'label' => false,
                'attr' => ['class' => 'simple-datepicker'],
                'required' => true,
            ])
            ->add('end_date', Form\TextType::class, [
                'label' => false,
                'attr' => ['class' => 'simple-datepicker'],
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-warning',
                    'title' => 'Recharger',
                ],
                'label' => '',
            ])
            ->add('submit_all', Form\SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-warning',
                    'formnovalidate' => 'formnovalidate',
                ],
                'label' => '',
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $data = $form->getData();

                $startDate = $data['start_date'] ? \DateTimeImmutable::createFromFormat('d/m/Y', $data['start_date']) : null;
                $endDate = $data['end_date'] ? \DateTimeImmutable::createFromFormat('d/m/Y', $data['end_date']) : null;
                $filename = $dataExporter->exportPlace($place, $startDate, $endDate);
                $file =new File($filename);

                $response = new BinaryFileResponse($file);
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

                return $response;
            }
        }

        return $this->render('configuration/place_export.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
            'cancel' => 'config'
        ]);
    }

    /**
     * @Route("/configuration/user", name="config.user.update")
     */
    public function userUpdateAction(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $userForm = $this->createForm(UpdateAccountType::class, $this->getUser(), [
            'data_class' => null,
            'constraints' => [
                new UniqueUsername(),
                new UpdatePassword(),
            ],
        ]);

        if('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                UpdateAccountType::handleSubmit($entityManager, $passwordEncoder, $userForm->getData());
                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');
                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/users_form.html.twig', [
            'title' => 'Mettre à jour ses données',
            'user_form' => $userForm->createView()
        ]);
    }
}
