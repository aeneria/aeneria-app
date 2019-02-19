<?php

namespace App\Validator\Constraints;

use App\FeedObject\Linky;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class LogsToEnedisValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LogsToEnedis) {
            throw new UnexpectedTypeException($constraint, LogsToEnedis::class);
        }

        $linky = new Linky($value, null);

        if (!$linky->isAuth()) {
            $this->context->addViolation('');
            $this->context->getRoot()->addError(new FormError($constraint->message));
        }
    }
}
