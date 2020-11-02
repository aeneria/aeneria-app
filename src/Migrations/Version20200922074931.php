<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale
 */
final class Version20200922074931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration initiale';
    }

    public function up(Schema $schema): void
    {
        // Cette migration ne sert qu'à permettre l'initialisation du système de migration à l'installation
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
