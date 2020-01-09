<?php

namespace App\Validator\Constraints;

use App\Services\FeedDataProvider\LinkyDataProvider;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LogsToEnedisValidator extends ConstraintValidator
{
    private $linkyDataProvider;

    public function __construct(LinkyDataProvider $linkyDataProvider)
    {
        $this->linkyDataProvider = $linkyDataProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LogsToEnedis) {
            throw new UnexpectedTypeException($constraint, LogsToEnedis::class);
        }
        $feedParam = $value->getParam();

        if (!$this->linkyDataProvider->auth($feedParam['LOGIN'], $feedParam['PASSWORD'])) {
            $this->context->addViolation('');
            $this->context->getRoot()->addError(new FormError($constraint->message));
        }
    }
}
