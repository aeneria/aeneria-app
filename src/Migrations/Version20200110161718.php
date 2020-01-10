<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Feed;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200110161718 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE feed SET feed_data_provider_type = "' . Feed::FEED_DATA_PROVIDER_LINKY . '" WHERE feed_type="LINKY";');
        $this->addSql('UPDATE feed SET feed_data_provider_type = "' . Feed::FEED_DATA_PROVIDER_METEO_FRANCE . '" WHERE feed_type="METEO_FRANCE";');

        $this->addSql('UPDATE feed SET feed_type = "' . Feed::FEED_TYPE_ELECTRICITY . '" WHERE feed_type="LINKY";');
        $this->addSql('UPDATE feed SET feed_type = "' . Feed::FEED_TYPE_METEO . '" WHERE feed_type="METEO_FRANCE";');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
