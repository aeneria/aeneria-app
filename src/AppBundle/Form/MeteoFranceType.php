<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Object\MeteoFrance;

class MeteoFranceType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $stations = MeteoFrance::getAvailableStations();

    $builder
    ->add('Nom', TextType::class)
    ->add('Stations', ChoiceType::class, [
        'choices' => $stations,
    ])
    ->add('Enregister', SubmitType::class);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
