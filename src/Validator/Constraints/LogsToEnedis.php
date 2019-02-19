<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LogsToEnedis extends Constraint
{
    public $message = 'La connexion au site Enedis a échoué, l\'adresse email ou le mot de passe n\'est pas bon, ou bien le site d\'Enedis n\'est pas disponible';

    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
