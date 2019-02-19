<?php

namespace App\Form;

use App\Entity\Feed;
use App\FeedObject\MeteoFrance;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

class MeteoFranceFeedType extends AbstractType
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Set MeteoStation parameter.
        $stations = MeteoFrance::getAvailableStations();
        $builder
            ->add('station', ChoiceType::class, [
                'choices' => $stations,
                'label' => 'Station d\'observation',
                'required' => TRUE,
            ])
            ->add('id', HiddenType::class)
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function (Feed $meteoFranceFeed) {
                $data['id'] = $meteoFranceFeed->getId();

                $param = $meteoFranceFeed->getParam();
                $data['station'] = (int)$param['STATION_ID'];

                return $data;
            },
            function (array $data) use ($stations) {
                $meteoFranceFeed = $data['id'] ? $this->entityManager->getRepository('App:Feed')->find($data['id']) : null;

                if (!$meteoFranceFeed) {
                    $meteoFranceFeed = new Feed();
                    $meteoFranceFeed
                        ->setFeedType('METEO_FRANCE')
                        ->setName('meteo')
                        ->setPublic(true)
                        ->setCreator(0)
                    ;
                }

                $param = [
                    'STATION_ID' => $data['station'],
                    'CITY' => array_search($data['station'], $stations),
                ];
                $meteoFranceFeed->setParam($param);

                return $meteoFranceFeed;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }
}
