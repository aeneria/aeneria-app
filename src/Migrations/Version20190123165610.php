<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Migration, nothing to do there !
 */
final class Version20190123165610 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // Pouet pouet
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
