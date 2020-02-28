<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUsername extends Constraint
{
    public $message = 'Un utilisateur avec le même nom existe déjà !';

    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
