<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111141112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE feed ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE place ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE place ALTER updated_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
