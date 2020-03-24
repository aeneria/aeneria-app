<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpdatePasswordValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $userRepository;
    protected $passwordEncoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UpdatePassword) {
            throw new UnexpectedTypeException($constraint, UpdatePassword::class);
        }

        if ($value['new_password']) {
            $user = $value['user'];
            \assert($user instanceof User);

            if (!$this->passwordEncoder->isPasswordValid($user, $value['old_password'])) {
                $this->context->getRoot()->get('old_password')->addError(
                    new FormError("Le mot de passe renseigné ne correspond pas au mot de passe actuelle.")
                );
            }

            if ($value['new_password'] !== $value['new_password2']) {
                $this->context->getRoot()->get('new_password2')->addError(
                    new FormError("Les 2 mots de passe ne sont pas identiques.")
                );
                $this->context->getRoot()->get('new_password')->addError(
                    new FormError("Les 2 mots de passe ne sont pas identiques.")
                );
            }
        }
    }
}
