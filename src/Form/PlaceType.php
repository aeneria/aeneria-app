<?php

namespace App\Form;

use App\Entity\Place;
use App\Entity\User;
use App\Form\LinkyFeedType;
use App\Form\MeteoFranceFeedType;
use App\Repository\PlaceRepository;
use App\Repository\UserRepository;
use App\Validator\Constraints\LogsToEnedis;
use Doctrine\ORM\EntityManagerInterface;
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
                'label' => 'Icone'
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Public',
                'help' => 'Un compteur public est visible par tous les utilisateurs de Pilea.',
                'required' => false
            ])
            ->add('shared', ChoiceType::class, [
                'multiple' => true,
                'label' => 'Partager avec :',
                'choices' => $this->userRepository->getUsersList($options['user']),
                'attr' => ['class' => 'bootstrap-multiselect'],
                'required' => false,
            ])
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
                        ->setPublic($data['public'])
                        ->setAllowedUsers($this->userRepository->findById($data['shared']))
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
        ]);
    }

    public static function handleSubmit(EntityManagerInterface $entityManager, Place $place, User $user)
    {
        $place->setUser($user);

        $entityManager->persist($place);
        $feedRepository = $entityManager->getRepository('App:Feed');

        foreach ($place->getFeeds() as $feed) {
            $entityManager->persist($feed);
            $feedRepository->createDependentFeedData($feed);
        }

        $entityManager->flush();
    }
}
