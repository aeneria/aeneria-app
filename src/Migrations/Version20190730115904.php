<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Doctrine\ORM\Query\ResultSetMapping;
use App\Repository\FeedDataRepository;
use App\FeedObject\MeteoFrance;
use App\Entity\DataValue;
use Doctrine\ORM\NativeQuery;

/**
 * Empty values from FeedData from MeteoFrance should be set to 0.
 */
final class Version20190730115904 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        // For each date, if we have data for one of the meteo feeddata
        // we should have data for nebulosity, DJU and rain but until
        // now, if the data was 0, there's nothing in database.
        //
        // We will rebuild theses data here.

        $doctrine = $this->container->get('doctrine');
        /** @var EntityManager $entityManager */
        $entityManager = $doctrine->getManager();
        $feedRepository = $doctrine->getRepository('App:Feed');
        /** @var FeedDataRepository $feedDataRepository */
        $feedDataRepository = $doctrine->getRepository('App:FeedData');

        if ($meteoFranceFeed = $feedRepository->findOneBy(['feedType' => 'METEO_FRANCE'])) {
            $meteoFrance = new MeteoFrance($meteoFranceFeed, $entityManager);
            $feedDatas = $feedDataRepository->findBy([
                'feed' => $meteoFranceFeed,
                'dataType' => ['DJU', 'NEBULOSITY', 'RAIN']
            ]);

            foreach($feedDatas as $feedData) {

                $rsm = new ResultSetMapping();
                $rsm->addScalarResult('date', 'date', 'datetime');

                $results = $entityManager
                    ->createNativeQuery('
                        SELECT distinct(data_value.date) as date FROM data_value
                        JOIN feed_data on feed_data.id = data_value.feed_data_id
                        JOIN feed on feed.id = feed_data.feed_id
                        WHERE feed.feed_type = "METEO_FRANCE"
                        AND data_value.frequency = 2
                        AND data_value.date NOT IN (
                            SELECT date FROM data_value WHERE feed_data_id = ? AND frequency = 2
                        )
                        ORDER BY data_value.date;
                    ', $rsm)
                    ->setParameter(1, $feedData->getId())
                    ->getResult(NativeQuery::HYDRATE_ARRAY)
                ;

                foreach($results as $result) {
                    $feedData->updateOrCreateValue(
                        $result['date'],
                        DataValue::FREQUENCY['DAY'],
                        0,
                        $entityManager
                    );
                    $meteoFrance->refreshAgregateValue($result['date']);
                }
            }
        }


    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException("Always move forward.");
    }
}
