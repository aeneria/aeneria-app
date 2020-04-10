<?php

namespace App\Form;

use App\Entity\Place;
use App\Repository\UserRepository;
use App\Validator\Constraints\LogsToEnedis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du compteur',
            ])
            ->add('icon', IconChoiceType::class, [
                'label' => 'Icone',
            ])
        ;

        if ($options['place_can_be_public']) {
            $builder->add('public', CheckboxType::class, [
                'label' => 'Public',
                'help' => 'Un compteur public est visible par tous les utilisateurs de æneria.',
                'required' => false,
            ]);
        }

        if ($options['user_can_share_place']) {
            $builder->add('shared', ChoiceType::class, [
                'multiple' => true,
                'label' => 'Partager avec :',
                'choices' => $this->userRepository->getUsersList($options['user']),
                'attr' => ['class' => 'bootstrap-multiselect'],
                'required' => false,
            ]);
        }

        $builder
            ->add('electricity', LinkyFeedType::class, [
                'label' => false,
                'constraints' => [
                    new LogsToEnedis(),
                ],
            ])
            ->add('meteo', MeteoFranceFeedType::class, [
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->addModelTransformer(new CallbackTransformer(
                function (?Place $place) {
                    if ($place) {
                        $data['place'] = $place;
                        $data['name'] = $place->getName();
                        $data['icon'] = $place->getIcon();

                        $data['public'] = $place->isPublic();

                        $data['shared'] = [];
                        foreach ($place->getAllowedUsers() as $user) {
                            $data['shared'][] = $user->getId();
                        }

                        foreach ($place->getFeeds() as $feed) {
                            $data[\strtolower($feed->getFeedType())] = $feed;
                        }

                        return $data;
                    }
                },
                function (array $data) {
                    $place = $data['place'] ?? null;

                    if (!$place) {
                        $place = new Place();
                    }
                    $place
                        ->setName($data['name'])
                        ->setIcon($data['icon'])
                        ->setPublic($data['public'] ?? false)
                        ->setAllowedUsers($this->userRepository->findById($data['shared'] ?? []))
                        ->addFeed($data['meteo'])
                        ->addFeed($data['electricity'])
                    ;

                    return $place;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'user_can_share_place' => null,
            'place_can_be_public' => null,
        ]);
    }
}
