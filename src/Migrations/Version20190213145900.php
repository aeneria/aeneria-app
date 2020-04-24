<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Place;
use App\Repository\FeedDataRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Populate Place table and add contraints.
 */
final class Version20190213145900 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema): void
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $feedDataRepository = $this->container->get(FeedDataRepository::class);

        // Create place for Linky Feed.
        $linkyFeed = $feedDataRepository->findOneByFeedType('LINKY');

        if ($linkyFeed) {
            $param = $linkyFeed->getParam();
            $address = $param['ADDRESS'];

            $place = new Place();
            $place->setName($address);
            $place->setPublic(true);
            $place->setCreator(1);

            $linkyFeed->setPlace($place);

            // Link MeteoFrance feed with place.
            $meteoFranceFeed = $feedDataRepository->findOneByFeedType('METEO_FRANCE');

            $meteoFranceFeed->setPlace($place);

            $entityManager->persist($place);
            $entityManager->persist($linkyFeed);
            $entityManager->persist($meteoFranceFeed);
            $entityManager->flush();
        }

        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044ABDA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
