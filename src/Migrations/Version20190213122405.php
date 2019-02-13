<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Add Place table and update feed.
 */
final class Version20190213122405 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // Update databse: add place table and update feed.
        $this->addSql('
            CREATE TABLE place (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                public TINYINT(1) NOT NULL,
                creator INT NOT NULL,
                UNIQUE INDEX UNIQ_E16F61D45E237E06 (name),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE feed
            ADD place_id INT NOT NULL,
            CHANGE name name VARCHAR(150) NOT NULL,
            CHANGE feed_type feed_type VARCHAR(150) NOT NULL
        ');

        $this->addSql('CREATE INDEX IDX_234044ABDA6A219 ON feed (place_id)');

        $this->addSql('
            ALTER TABLE feed_data
            CHANGE data_type data_type VARCHAR(150) NOT NULL
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
