<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221017152308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialize fetch_error column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE feed set fetch_error = coalesce((param->>'FETCH_ERROR')::INT, 0);");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
