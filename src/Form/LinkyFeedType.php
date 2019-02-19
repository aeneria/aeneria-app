<?php

namespace App\Form;

use App\Entity\Feed;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class LinkyFeedType extends AbstractType
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
            ->add('login', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'always_empty' => FALSE,
            ])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function (Feed $linkyFeed) {
                $data['id'] = $linkyFeed->getId();
                $param = $linkyFeed->getParam();

                $data['name'] = $linkyFeed->getName();
                foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $paramName => $label) {
                    $data[strtolower($paramName)] = $param[$paramName];
                }

                return $data;
            },
            function (array $data) {
                $linkyFeed = $data['id'] ? $this->entityManager->getRepository('App:Feed')->find($data['id']) : null;

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
                foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
                    $param[$name] = $data[strtolower($name)];
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
