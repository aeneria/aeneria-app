<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Validator\Constraints\AtLeastOneAdmin;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdministrationController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin.user.list")
     */
    public function userListAction(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $users = $userRepository->findAll();

        return $this->render('administration/users_list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/users/add", name="admin.user.add")
     */
    public function addUserAction(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $userForm = $this->createForm(UserType::class, null, [
                'data_class' => null,
            ])
        ;

        if('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                UserType::handleSubmit($entityManager, $userForm->getData());
                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');
                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('administration/users_form.html.twig', [
            'title' => 'Ajouter un utilisateur',
            'user_form' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/users/{id}/update", name="admin.user.update")
     */
    public function updateUserAction(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this
            ->getDoctrine()
            ->getRepository('App:User')
            ->find($id)
        ;

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $userForm = $this->createForm(UserType::class, $user, [
            'data_class' => null,
        ]);

        if('POST' === $request->getMethod()) {
            $userForm->handleRequest($request);
            if ($userForm->isValid()) {
                UserType::handleSubmit($entityManager, $userForm->getData());
                $this->addFlash('success', 'L\'utilisateur a bien été enregistrée !');
                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('administration/users_form.html.twig', [
            'title' => 'Mettre à jour un utilisateur',
            'user_form' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/users/{id}/delete", name="admin.user.delete")
     */
    public function removeUserAction(Request $request, EntityManagerInterface $entityManager, string $id)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this
            ->getDoctrine()
            ->getRepository('App:User')
            ->find($id)
        ;

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

        if('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                $user->setActive(false);
                $entityManager->flush();
                $this->addFlash('success', 'L\'utilisateur a bien été désactivé !');
                return $this->redirectToRoute('admin.user.list');
            }
        }

        return $this->render('administration/confirmation_form.html.twig', [
        'title' => 'Désactiver un utilisateur',
        'form' => $form->createView()
        ]);
    }
}
