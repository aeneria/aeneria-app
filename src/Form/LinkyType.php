<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Feed;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

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
        $type = $name !== 'PASSWORD' ? TextType::class : PasswordType::class;
        $builder->add(strtolower($name), $type, [
            'label' => $label,
        ]);
    }

    $builder->add('save', SubmitType::class, [
        'label' => 'Enregistrer',
    ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
