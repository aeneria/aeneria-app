<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AtLeastOneAdmin extends Constraint
{
    public $message = 'Vous ne pouvez pas désactiver cet utilisateur, c\'est le dernier administrateur !';

    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
