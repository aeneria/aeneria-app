<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Feed;
use App\FeedObject\MeteoFrance;

class ConfigurationType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('address', TextType::class, [
        'label' => 'Adresse du compteur',
    ])
    ->add('login', EmailType::class, [
        'label' => 'Adresse email',
    ])
    ->add('password', PasswordType::class, [
        'label' => 'Mot de passe',
    ]);

    // Set MeteoStation parameter.
    $stations = MeteoFrance::getAvailableStations();
    $builder->add('station', ChoiceType::class, [
        'choices' => $stations,
        'label' => 'Station d\'observation',
        'required' => TRUE,
    ]);

    $builder->add('save', SubmitType::class, [
        'label' => 'Enregistrer',
    ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
