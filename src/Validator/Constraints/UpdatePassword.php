<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UpdatePassword extends Constraint
{
    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
