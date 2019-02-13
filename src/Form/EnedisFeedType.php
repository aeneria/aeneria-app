<?php

namespace App\Form;

use App\Entity\Feed;
use App\FeedObject\Linky;
use App\FeedObject\MeteoFrance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EnedisFeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse du compteur',
            ])
            ->add('login', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'always_empty' => FALSE,
                'constraints' => array(
                    new Callback(function ($object, ExecutionContextInterface $context) {
                        self::enedisValidation($object, $context);
                    }),
                ),
            ]);

        // Set MeteoStation parameter.
        $stations = MeteoFrance::getAvailableStations();
        $builder->add('station', ChoiceType::class, [
            'choices' => $stations,
            'label' => 'Station d\'observation',
            'required' => TRUE,
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Enregistrer',
        ]);

        $builder->addModelTransformer(new CallbackTransformer(
            /** @var $fichiers Feed[] */
            function (array $feeds) {
                [$linky, $meteoFrance] = $feeds;
                if ($linky) {
                    $data['name'] = $linky->getName();
                    $param = $linky->getParam();
                    foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $paramName => $label) {
                        $data[strtolower($paramName)] = $param[$paramName];
                    }
                }
                if ($meteoFrance) {
                    $param = $meteoFrance->getParam();
                    $data['station'] = (int)$param['STATION_ID'];
                }
                return $data;
            },
            function (array $data) use ($stations) {
                // Create Linky feed.
                $linky = new Feed();
                $linky->setFeedType('LINKY');
                $linky->setName('linky');
                $param = [];
                foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
                    $param[$name] = $data[strtolower($name)];
                }
                $linky->setParam($param);

                // Create MeteoFrance feed.
                $meteoFrance = new Feed();
                $meteoFrance->setFeedType('METEO_FRANCE');
                $meteoFrance->setName('meteo');
                $param = [
                    'STATION_ID' => $data['station'],
                    'CITY' => array_search($data['station'], $stations),
                ];
                $meteoFrance->setParam($param);

                return [$linky, $meteoFrance];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public static function enedisValidation($object, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();
        $linky = new Linky($data[0], NULL);

        if (!$linky->isAuth()) {
            $context->addViolation('');
            $context->getRoot()->addError(new FormError('La connexion au site Enedis a échoué, l\'adresse email ou le mot de passe n\'est pas bon, ou bien le site d\'Enedis n\'est pas disponible'));
        }
    }

    public function handleSubmit(ObjectManager $entityManager, $oldFeeds = [], $updatedFeeds) {

        foreach ($updatedFeeds as $key => $updateFeed) {
            if (isset($oldFeeds[$key])) {
                $oldFeed = $oldFeeds[$key];
            }
            else {
                $oldFeed = new Feed();
                $oldFeed->setCreator('admin'); //@TODO Get yunohost user
                $oldFeed->setPublic(true); //@TODO Deal with yunohost users
            }

            $oldFeed->setName($updateFeed->getName());
            $oldFeed->setFeedType($updateFeed->getFeedType());
            $oldFeed->setParam($updateFeed->getParam());
            $oldFeed->createDependentFeedData($entityManager);
            $entityManager->persist($oldFeed);
        }

        $entityManager->flush();
    }
}
