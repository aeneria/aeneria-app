<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108152220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed ALTER fetch_error DROP DEFAULT');
        $this->addSql('ALTER TABLE users ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL');
        $this->addSql('ALTER TABLE users ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL');
        $this->addSql('ALTER TABLE users ADD last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
