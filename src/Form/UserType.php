<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\Constraints\AtLeastOneAdmin;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserType extends AbstractType
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom de l\'utilisateur',
                'constraints' => [
                    new AtLeastOneAdmin(),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'always_empty' => FALSE,
                'required' => !$data ?? FALSE
            ])
            ->add('is_admin', CheckboxType::class, [
                'label' => 'L\'utilisateur est Administrateur',
                'help' => 'S\'il est administrateur, il pourra ajouter/modifier/supprimer les autres utilisateurs',
                'required' => FALSE
            ])
            ->add('is_active', CheckboxType::class, [
                'label' => 'L\'utilisateur est actif',
                'required' => FALSE,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->addModelTransformer(new CallbackTransformer(
                function (?User $user) {
                    if ($user) {
                        $data['user'] = $user;
                        $data['username'] = $user->getUsername();
                        $data['is_admin'] = \in_array(User::ROLE_ADMIN, $user->getRoles());
                        $data['is_active'] = $user->isActive();

                        return $data;
                    }
                },
                function (array $data) {
                    $user = $data['user'] ?? null;

                    if (!$user) {
                        $user = new User();
                    }

                    $user->setUsername($data['username']);
                    $user->setActive($data['is_active']);

                    if ($data['password']) {
                        $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
                    }

                    $user->setRoles($data['is_admin'] ? ['ROLE_ADMIN'] : []);

                    return $user;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public static function handleSubmit(EntityManagerInterface $entityManager, User $user)
    {
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
