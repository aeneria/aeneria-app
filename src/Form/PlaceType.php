<?php

namespace App\Form;

use App\Entity\Place;
use App\Form\LinkyFeedType;
use App\Form\MeteoFranceFeedType;
use App\Validator\Constraints\LogsToEnedis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ObjectManager;

class PlaceType extends AbstractType
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => 'Nom du compteur',
            ])
            ->add('linky', LinkyFeedType::class, [
                'label' => false,
                'constraints' => [
                    new LogsToEnedis(),
                ],
            ])
            ->add('meteo_france', MeteoFranceFeedType::class, [
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->addModelTransformer(new CallbackTransformer(
                function (Place $place) {
                    $data['id'] = $place->getId();
                    $data['name'] = $place->getName();

                    foreach ($place->getFeeds() as $feed) {
                        $data[\strtolower($feed->getFeedType())] = $feed;
                    }

                    return $data;
                },
                function (array $data) {
                    $place = $data['id'] ? $this->entityManager->getRepository('App:Place')->find($data['id']) : null;

                    if (!$place) {
                        $place = new Place();
                        $place
                            ->setPublic(true)
                            ->setCreator(0)
                        ;
                    }
                    // $place->removeAllFeeds();

                    $place
                        ->setName($data['name'])
                        ->addFeed($data['linky'])
                        ->addFeed($data['meteo_france'])
                    ;

                    return $place;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function handleSubmit(ObjectManager $entityManager, Place $place)
    {
        $entityManager->persist($place);

        foreach ($place->getFeeds() as $feed) {
            $entityManager->persist($feed);
        }

        $entityManager->flush();
    }
}
