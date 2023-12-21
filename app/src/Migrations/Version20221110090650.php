<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221110090650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de champs created_at, updated_at et last_login sur les Users';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER updated_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
