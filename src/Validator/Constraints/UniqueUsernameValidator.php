<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueUsernameValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueUsername) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
        }

        if ($this->userRepository->findByUsername($value)) {
            $this->context->getRoot()->addError(new FormError($constraint->message));
        }
    }
}
