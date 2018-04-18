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
      // We get the current MeteoFrance feed if there's one
      $meteoFrance = $this->
      // We get the list of Meteo France SYNOP observation stations
      $stations = MeteoFrance::getAvailableStations();

      $builder
      ->add('name', TextType::class, [
          'label' => 'Nom',
          'required' => TRUE,
      ])
      ->add('station', ChoiceType::class, [
          'choices' => $stations,
          'label' => 'Station',
          'required' => TRUE,
      ])
      ->add('save', SubmitType::class, [
          'label' => 'Enregistrer',
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
