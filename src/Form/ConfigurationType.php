<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Feed;
use App\FeedObject\MeteoFrance;
use App\FeedObject\Linky;
use Symfony\Component\Form\FormError;

class ConfigurationType extends AbstractType
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
            'required' => true,
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Enregistrer',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public static function enedisValidation($object, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();

        $linkyFeed = new Feed();
        $param = [];
        foreach (Feed::FEED_TYPES['LINKY']['PARAM'] as $name => $label) {
            $param[$name] = $data[strtolower($name)];
        }
        $linkyFeed->setParam($param);
        $linky = new Linky($linkyFeed, null);

        if (!$linky->isAuth()) {
            $context->addViolation('');
            $context->getRoot()->addError(new FormError('La connexion au site Enedis a échoué, l\'adresse email ou le mot de passe n\'est pas bon, ou bien le site d\'Enedis n\'est pas disponible'));
        }

    }
}
