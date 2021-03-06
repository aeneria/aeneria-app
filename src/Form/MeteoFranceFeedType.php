<?php

namespace App\Form;

use App\Entity\Feed;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeteoFranceFeedType extends AbstractType
{
    private $meteoFranceDataProvider;

    public function __construct(MeteoFranceDataProvider $meteoFranceDataProvider)
    {
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Set MeteoStation parameter.
        $stations = $this->meteoFranceDataProvider->getAvailableStations();
        $builder
            ->add('station', ChoiceType::class, [
                'choices' => $stations,
                'label' => 'Station d\'observation',
                'required' => true,
            ])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function (?Feed $meteoFranceFeed) {
                if ($meteoFranceFeed) {
                    $param = $meteoFranceFeed->getParam();
                    $data['station'] = $param['STATION_ID'] ?? null;

                    return $data;
                }
            },
            function (array $data) use ($stations) {
                return [
                    'STATION_ID' => $data['station'] ?? null,
                    'CITY' => \array_search($data['station'], $stations),
                ];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
