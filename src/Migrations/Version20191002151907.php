<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191002151907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD IF NOT EXISTS active TINYINT(1) DEFAULT \'1\' NOT NULL');

        $this->addSql('DROP INDEX IF EXISTS UNIQ_741D53CD5E237E06 ON place');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_E16F61D45E237E06 ON place');

        $this->addSql('ALTER TABLE feed DROP public, DROP creator');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_234044ABC49BC7E ON feed');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_234044AB5E237E06 ON feed');

        $this->addSql('DROP INDEX IF EXISTS UNIQ_2D64183437919CCB ON feed_data');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
