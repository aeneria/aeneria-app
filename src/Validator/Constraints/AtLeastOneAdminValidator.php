<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AtLeastOneAdminValidator extends ConstraintValidator
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
        if (!$constraint instanceof AtLeastOneAdmin) {
            throw new UnexpectedTypeException($constraint, AtLeastOneAdmin::class);
        }

        if ($value instanceof User) {
            $username = $value->getUsername();
        } else {
            $username = $value['username'];
        }

        if ($this->userRepository->isLastAdmin($username)) {
            $this->context->getRoot()->addError(new FormError($constraint->message));
        }
    }
}
