<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Cean -1 values from FeedData from Enedis.
 */
final class Version20190909180600 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema): void
    {
        // Cean -1 values from FeedData from Enedis.
        $this->addSql('
            DELETE data_value.*
            FROM data_value
            JOIN feed_data
                ON feed_data.id = data_value.feed_data_id
                AND feed_data.data_type = "CONSO_ELEC"
            WHERE data_value.value = "-1"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
