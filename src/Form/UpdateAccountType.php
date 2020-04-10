<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom de l\'utilisateur',
            ])
            ->add('old_password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'always_empty' => false,
                'required' => !$data ?? false,
            ])
            ->add('new_password', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => false,
            ])
            ->add('new_password2', PasswordType::class, [
                'label' => 'Confirmez votre nouveau mot de passe',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->addModelTransformer(new CallbackTransformer(
                function (?User $user) {
                    if ($user) {
                        $data['user'] = $user;
                        $data['username'] = $user->getUsername();

                        return $data;
                    }
                },
                function (array $data) {
                    return $data;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
