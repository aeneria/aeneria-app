<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use App\Entity\Place;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Populate Place table and add contraints.
 */
final class Version20190213145900 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getManager();

        // Create place for Linky Feed.
        $linkyFeed = $doctrine
            ->getRepository('App:Feed')
            ->findOneByFeedType('LINKY');
        if ($linkyFeed) {
            $param = $linkyFeed->getParam();
            $address = $param['ADDRESS'];

            $place = new Place();
            $place->setName($address);
            $place->setPublic(true);
            $place->setCreator(1);

            $linkyFeed->setPlace($place);

            // Link MeteoFrance feed with place.
            $meteoFranceFeed = $doctrine
                ->getRepository('App:Feed')
                ->findOneByFeedType('METEO_FRANCE');

            $meteoFranceFeed->setPlace($place);

            $entityManager->persist($place);
            $entityManager->persist($linkyFeed);
            $entityManager->persist($meteoFranceFeed);
            $entityManager->flush();
        }

        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044ABDA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
