<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdateAccountType;
use App\Repository\UserRepository;
use App\Services\DataExporter;
use App\Services\PendingActionService;
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
     * Delete user account form view
     */
    public function userDeleteAction(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->checkUser();

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
