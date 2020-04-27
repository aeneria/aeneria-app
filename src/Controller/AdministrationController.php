<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Validator\Constraints\AtLeastOneAdmin;
use App\Validator\Constraints\UniqueUsername;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AdministrationController extends AbstractController
{
    /**
     * User list view
     */
    public function userListAction(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $users = $userRepository->findAll();

        return $this->render('administration/users_list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Add new user form view
     */
    public function addUserAction(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $userForm = $this->createForm(UserType::class, null, [
                'data_class' => null,
                'constraints' => [
                    new AtLeastOneAdmin(),
                    new UniqueUsername(),
                ],
            ])
        ;

        if ('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                $entityManager->persist($userForm->getData());
                $entityManager->flush();

                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');

                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('administration/users_form.html.twig', [
            'title' => 'Ajouter un utilisateur',
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * Update user form view
     */
    public function updateUserAction(Request $request, $id, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $userForm = $this->createForm(UserType::class, $user, [
            'data_class' => null,
            'constraints' => [
                new Callback(['callback' => static function ($data, ExecutionContextInterface $context) use ($userRepository) {
                    if (!$data->isActive() || !$data->isAdmin()) {
                        if ($userRepository->isLastAdmin($data->getUsername())) {
                            $context
                                    ->buildViolation("Vous ne pouvez pas désactiver cet utilisateur, c'est le dernier administrateur !")
                                    ->addViolation()
                                ;
                        }
                    }
                },
                ]),
                new UniqueUsername(),
            ],
        ]);

        if ('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                $entityManager->persist($userForm->getData());
                $entityManager->flush();

                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');

                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('administration/users_form.html.twig', [
            'title' => 'Mettre à jour un utilisateur',
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * Disable user form view
     */
    public function disableUserAction(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, string $id)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $form = $this
            ->createFormBuilder()
            ->add('username', Form\HiddenType::class, [
                'data' => $user->getUsername(),
                'constraints' => [
                    new AtLeastOneAdmin(),
                ],
            ])
            ->add('are_you_sure', Form\CheckboxType::class, [
                'label' => "Veuillez cocher cette case si vous êtes sûr de vouloir désactiver cet utilisateur",
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
                'label' => "Désactiver l'utilisateur",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $user->setActive(false);
                $entityManager->flush();
                $this->addFlash('success', 'L\'utilisateur a bien été désactivé !');

                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Désactiver un utilisateur',
            'form' => $form->createView(),
            'cancel' => 'admin.user.list',
        ]);
    }

    /**
     * Delete user form veiw
     */
    public function deleteUserAction(Request $request, UserRepository $userRepository, string $id)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $form = $this
            ->createFormBuilder()
            ->add('username', Form\HiddenType::class, [
                'data' => $user->getUsername(),
                'constraints' => [
                    new AtLeastOneAdmin(),
                ],
            ])
            ->add('are_you_sure', Form\CheckboxType::class, [
                'label' => "Veuillez cocher cette case si vous êtes sûr de vouloir supprimer cet utilisateur",
                'help' => "Attention, cette action entrainera la suppression de TOUTES les données associées à cet utilisateurs.",
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
                'label' => "Supprimer l'utilisateur et TOUTES ses données",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $userRepository->purge($user);
                $this->addFlash('success', 'L\'utilisateur a bien été supprimé !');

                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Supprimer un utilisateur',
            'form' => $form->createView(),
            'cancel' => 'admin.user.list',
        ]);
    }

    /**
     * Log view
     */
    public function displayLogAction(ContainerInterface $container)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $logDir = $container->get('kernel')->getLogDir();

        $latestCTime = 0;
        $latestLogfile = '';
        if ($dirHandle = \dir($logDir)) {
            while (false !== ($entry = $dirHandle->read())) {
                $filepath = "{$logDir}/{$entry}";
                if (\is_file($filepath) && \filectime($filepath) > $latestCTime) {
                    $latestCTime = \filectime($filepath);
                    $latestLogfile = $entry;
                }
            }
        }

        return $this->render('administration/log.html.twig', [
            'title' => 'Supprimer un utilisateur',
            'logs' => \file("{$logDir}/{$latestLogfile}", \FILE_IGNORE_NEW_LINES), //\file_get_contents("{$logDir}/{$latestLogfile}") ?? false,
            'cancel' => 'admin.user.list',
        ]);
    }
}
