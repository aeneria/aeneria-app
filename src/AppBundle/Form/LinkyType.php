<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkyType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
    ->add('Nom_du_compteur',      TextType::class)
    ->add('Adresse_e-mail',     TextType::class)
    ->add('Mot_de_passe',    TextType::class)
    ->add('Enregistrer',      SubmitType::class);
  }

  public function configureOptions(OptionsResolver $resolver)
  {

  }
}
