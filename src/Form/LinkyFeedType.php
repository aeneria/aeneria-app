<?php

namespace App\Form;

use App\Entity\Feed;
use App\Services\FeedDataProvider\LinkyDataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkyFeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'always_empty' => FALSE,
                'required' => FALSE
            ])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function (?Feed $linkyFeed) {
                if($linkyFeed) {
                    $data['feed'] = $linkyFeed;
                    $param = $linkyFeed->getParam();

                    $data['name'] = $linkyFeed->getName();
                    foreach (\array_keys(LinkyDataProvider::getParametersName($linkyFeed)) as $paramName) {
                        $data[\strtolower($paramName)] = $param[$paramName];
                    }

                    return $data;
                }
            },
            function (array $data) {
                $linkyFeed = $data['feed'] ?? null;

                if (!$linkyFeed) {
                    $linkyFeed = new Feed();
                    $linkyFeed
                        ->setFeedType(Feed::FEED_TYPE_ELECTRICITY)
                        ->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_LINKY)
                        ->setName('linky')
                    ;
                }

                $param = [];
                foreach (array_keys(LinkyDataProvider::getParametersName($linkyFeed)) as $name) {
                    $param[$name] = $data[\strtolower($name)] ?? $linkyFeed->getParam()[$name] ?? null;
                }
                $linkyFeed->setParam($param);

                return $linkyFeed;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entityManager' => null,
        ]);
    }
}
