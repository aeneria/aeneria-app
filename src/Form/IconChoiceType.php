<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconChoiceType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Pass this flag is necessary to render the label as raw.
        // See below the twig field template for more details.
        $view->vars['raw_label'] = true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => "bootstrap-multiselect-fa",
            ],
            'choices' => $this->getFontAwesomeIconChoices(),
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    protected function getFontAwesomeIconChoices()
    {
        return [
            '&#xf1ad' => 'building',
            '&#xf557' => 'archay',
            '&#xf6bb' => 'campground',
            '&#xf64f' => 'city',
            '&#xf6d9' => 'dungeon',
            '&#xf015' => 'home',
            '&#xf0f8' => 'hospital',
            '&#xf594' => 'hotel',
            '&#xf7ae' => 'igloo',
            '&#xf275' => 'industry',
            '&#xf66f' => 'landmark',
            '&#xf67f' => 'place-of-worship',
            '&#xf549' => 'school',
            '&#xf54e' => 'store',
            '&#xf54f' => 'store-alt',
            '&#xf6a1' => 'torii-gate',
            '&#xf6a7' => 'vihara',
            '&#xf494' => 'warehouse',
            '&#xf004' => 'heart',
            '&#xf521' => 'crown',
            '&#xf164' => 'thumbs-up',
            '&#xf005' => 'star',
            '&#xf3c5' => 'map-marker-alt',
            '&#xf7a2' => 'globe-europe',
        ];
    }
}
