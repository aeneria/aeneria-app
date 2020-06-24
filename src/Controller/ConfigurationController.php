<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdateAccountType;
use App\Repository\UserRepository;
use App\Services\DataExporter;
use App\Services\FeedDataProvider\GenericFeedDataProvider;
use App\Validator\Constraints\AtLeastOneAdmin;
use App\Validator\Constraints\UniqueUsername;
use App\Validator\Constraints\UpdatePassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ConfigurationController extends AbstractAppController
{
    /**
     * Configuration view
     */
    public function configAction(Request $request)
    {
        // We get a Place if it already exists.
        $places = $this->placeRepository->findByUser($this->getUser());

        return $this->render('configuration/configuration.html.twig', [
            'places' => $places,
        ]);
    }

    /**
     * Fetch Place data form view
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
                    'help' => 'METEO' === $feed->getFeedType() ?
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
                    'constraints' => [new Assert\Callback([
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
                        },
                    ])],
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

        if ('POST' === $request->getMethod()) {
            foreach ($forms as $feedId => $form) {
                if ($request->request->has($feedId) && $form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();

                    $startDate = \DateTimeImmutable::createFromFormat('!d/m/Y', $data['start_date_' . $feedId]);
                    $endDate = \DateTimeImmutable::createFromFormat('!d/m/Y', $data['end_date_' . $feedId]);

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
            'cancel' => 'config',
        ]);
    }

    /**
     * Export Place data form view
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
                    'title' => 'Exporter',
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

        if ('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $data = $form->getData();

                $startDate = $data['start_date'] ? \DateTimeImmutable::createFromFormat('d/m/Y', $data['start_date']) : null;
                $endDate = $data['end_date'] ? \DateTimeImmutable::createFromFormat('d/m/Y', $data['end_date']) : null;
                $filename = $dataExporter->exportPlace($place, $startDate, $endDate);
                $file = new File($filename);

                $response = new BinaryFileResponse($file);
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

                return $response;
            }
        }

        return $this->render('configuration/place_export.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
            'cancel' => 'config',
        ]);
    }

    /**
     * Update account user form view
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

        if ('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                $data = $userForm->getData();

                $user = $data['user'];
                \assert($user instanceof User);

                $user->setUsername($data['username']);

                if ($data['new_password']) {
                    $user->setPassword($passwordEncoder->encodePassword($user, $data['new_password']));
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/account_form.html.twig', [
            'title' => 'Mettre à jour ses données',
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * Delete user account form view
     */
    public function userDeleteAction(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this
            ->createFormBuilder([], [
                'constraints' => [
                    new AtLeastOneAdmin(),
                ],
            ])
            ->add('username', Form\HiddenType::class, [
                'data' => $user->getUsername(),
            ])
            ->add('password', Form\PasswordType::class, [
                'label' => 'Mot de passe',
                'always_empty' => true,
                'required' => true,
                'constraints' => [
                    new Assert\Callback(['callback' => static function ($data, ExecutionContextInterface $context) use ($passwordEncoder, $user) {
                        if (!$passwordEncoder->isPasswordValid($user, $data)) {
                            $context
                                ->buildViolation("Mot de passe invalide.")
                                ->addViolation()
                            ;
                        }
                    },
                ]), ],
            ])
            ->add('are_you_sure', Form\CheckboxType::class, [
                'label' => "Veuillez cocher cette case si vous êtes sûr de vouloir supprimer votre compte et toutes ses données",
                'help' => "Cette action est irréversible !",
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger float-right'],
                'label' => "Supprimer mon compte et TOUTES mes données",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $userRepository->purge($user);
                $this->addFlash('success', 'Votre compte a bien été supprimé !');

                return $this->redirectToRoute('dashboard.home');
            }
        }

        $this->addFlash('danger', 'Vous êtes sur le point de supprimer votre compte, avant de vous lancer, avez-vous pensé à exporter vos données ? ' .
            'On ne sait jamais, si vous voulez revenir un jour, vous pourrez toujours les réimporter !'
        );

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Supprimer votre compte',
            'form' => $form->createView(),
            'cancel' => 'config',
        ]);
    }
}
