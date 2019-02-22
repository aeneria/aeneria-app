<?php

namespace App\Form;

use App\Entity\Feed;
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
            function (Feed $linkyFeed) {
                $data['feed'] = $linkyFeed;
                $param = $linkyFeed->getParam();

                $data['name'] = $linkyFeed->getName();
                foreach (\array_keys(Feed::FEED_TYPES['LINKY']['PARAM']) as $paramName) {
                    $data[\strtolower($paramName)] = $param[$paramName];
                }

                return $data;
            },
            function (array $data) {
                $linkyFeed = $data['feed'];

                if (!$linkyFeed) {
                    $linkyFeed = new Feed();
                    $linkyFeed
                        ->setFeedType('LINKY')
                        ->setName('linky')
                        ->setPublic(true)
                        ->setCreator(0)
                    ;
                }

                $param = [];
                foreach (array_keys(Feed::FEED_TYPES['LINKY']['PARAM']) as $name) {
                    $param[$name] = $data[\strtolower($name)] ?? $linkyFeed->getParam()[$name];
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
