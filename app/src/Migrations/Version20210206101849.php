<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206101849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<SQL
            CREATE TABLE notification (
                id INT NOT NULL,
                user_id INT NOT NULL,
                place_id INT DEFAULT NULL,
                type VARCHAR(100) NOT NULL,
                level VARCHAR(100) NOT NULL,
                date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                message VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CADA6A219 ON notification (place_id)');
        $this->addSql(<<<SQL
            ALTER TABLE notification
                ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id)
                REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<SQL
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADA6A219 FOREIGN KEY (place_id)
            REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Vaut mieux regarder devant soi que se retourner sur l'endroit où l'on a trébuché, proverbe Malien.");
    }
}
