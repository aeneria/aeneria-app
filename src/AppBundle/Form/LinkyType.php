<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Feed;

class LinkyType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
    ->add('name', TextType::class, [
        'label' => 'Nom du compteur',
        'required' => TRUE,
    ]);

    foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
      $builder->add(strtolower($name), TextType::class, [
          'label' => $label,
      ]);
    }

    $builder->add('save', SubmitType::class, [
        'label' => 'Enregistrer',
        'required' => TRUE,
    ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
