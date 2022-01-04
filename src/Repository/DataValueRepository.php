<?php

namespace App\Repository;

use App\Controller\DataController;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataValue[]    findAll()
 * @method DataValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataValue::class);
    }

    /**
     * Update or Create a new DataValue and persist it.
     */
    public function updateOrCreateValue(FeedData $feedData, \DateTimeImmutable $date, int $frequency, string $value)
    {
        // Update date according to frequnecy
        $date = DataValue::adaptToFrequency($date, $frequency);

        $criteria = [
            'feedData' => $feedData,
            'date' => $date,
            'frequency' => $frequency,
        ];

        // Try to get the corresponding DataValue.
        $dataValue = $this->findOneBy($criteria);

        // Create it if it doesn't exist.
        if (!isset($dataValue)) {
            $dataValue = (new DataValue())
                ->setFrequency($frequency)
                ->setFeedData($feedData)
                ->setDate($date)
                ->updateDateRelatedData()
            ;
        }

        $dataValue->setValue($value);

        // Persit the dataValue.
        $this->getEntityManager()->persist($dataValue);
    }

    /**
     * Agregate Values for a frequency and a date and persist it to EntityManager.
     */
    public function updateOrCreateAgregateValue(\DateTimeImmutable $date, Feed $feed, int $frequency)
    {
        list('from' => $firstDay, 'to' => $lastDay, 'previousFrequency' => $previousFrequency) = DataValue::getAdaptedBoundariesForFrequency($date, $frequency);

        if ($feedDatas = $feed->getFeedDatas()) {
            foreach ($feedDatas as $feedData) {
                switch ($feedData->getDataType()) {
                    case FeedData::FEED_DATA_DJU:
                    case FeedData::FEED_DATA_RAIN:
                    case FeedData::FEED_DATA_CONSO_ELEC:
                    case FeedData::FEED_DATA_CONSO_GAZ:
                        $agregateData = $this
                            ->getSumValue(
                                $firstDay,
                                $lastDay,
                                $feedData,
                                $previousFrequency
                            )
                        ;
                        break;
                    case FeedData::FEED_DATA_TEMPERATURE_MAX:
                        $agregateData = $this
                            ->getMaxValue(
                                $firstDay,
                                $lastDay,
                                $feedData,
                                $previousFrequency
                            )
                        ;
                        break;
                    case FeedData::FEED_DATA_TEMPERATURE_MIN:
                        $agregateData = $this
                            ->getMinValue(
                                $firstDay,
                                $lastDay,
                                $feedData,
                                $previousFrequency
                            )
                        ;
                        break;
                    default:
                        $agregateData = $this
                            ->getAverageValue(
                                $firstDay,
                                $lastDay,
                                $feedData,
                                $previousFrequency
                            )
                        ;
                        break;
                }

                if (isset($agregateData[0]['value'])) {
                    $this->updateOrCreateValue(
                        $feedData,
                        $firstDay,
                        $frequency,
                        \round($agregateData[0]['value'], 1)
                    );
                }
            }
        }
    }

    /**
     * Insert values between 2 dates for an array of FeedData and for a given frequency.
     *
     * Warning : Existing values with given criteria will be deleted in process !
     *
     * @param DataValue[] $dataValue
     */
    public function massImport(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $feedDatas, int $frequency, array $dataValues)
    {
        $this
            ->createQueryBuilder('d')
            ->delete()
            ->andWhere('d.feedData IN (:ids)')
            ->setParameter('ids', \array_map(function ($item) {
                return $item->getId();
            }, $feedDatas))
            ->andWhere('d.frequency = :freq')
            ->setParameter('freq', $frequency)
            ->andWhere('d.date BETWEEN :from AND :to')
            ->setParameter('from', $startDate)
            ->setParameter('to', $endDate)
            ->getQuery()
            ->execute()
        ;

        foreach ($dataValues as $dataValue) {
            $this->getEntityManager()->persist($dataValue);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Get an average value
     */
    public function getAverageValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FeedData $feedData, int $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('AVG(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get an minimum value
     */
    public function getMinValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FeedData $feedData, int $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MIN(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get an maximum value
     */
    public function getMaxValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FeedData $feedData, int $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MAX(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get sum of value
     *
     * @param FeedData|FeedData[] $feedData
     *
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|null
     */
    public function getSumValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, $feedData, int $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('SUM(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get XY
     *
     * @param FeedData|FeedData[] $feedDataX
     * @param FeedData|FeedData[] $feedDataY
     *
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|null
     */
    public function getXY(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, $feedDataX, $feedDataY, int $frequency)
    {
        $feedDataX = \is_array($feedDataX) ? $feedDataX : [$feedDataX];
        $feedDataY = \is_array($feedDataY) ? $feedDataY : [$feedDataY];

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('dx');

        $queryBuilder
            ->select('dx.value AS xValue, dy.value AS yValue, dx.date AS date')
            ->join(DataValue::class, 'dy', Join::WITH, 'dx.date = dy.date')
            // Add condition on dates
            ->andWhere('dx.date BETWEEN :start AND :end')
            ->andWhere('dy.date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            // Add condition on feedData
            ->andWhere($queryBuilder->expr()->in('dx.feedData', \array_map(function($feedData) {return $feedData->getId();}, $feedDataX)))
            ->andWhere($queryBuilder->expr()->in('dy.feedData', \array_map(function($feedData) {return $feedData->getId();}, $feedDataY)))
            // Add condition on frequency
            ->andWhere('dx.frequency = :frequency')
            ->andWhere('dy.frequency = :frequency')
            ->setParameter('frequency', $frequency)
            ->addGroupBy('dx.value')
            ->addGroupBy('dy.value')
            ->addGroupBy('dx.date')
            ->orderBy('dx.date', 'asc')
        ;

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get number of item inferior than value
     *
     * @param FeedData|FeedData[] $feedData
     */
    public function getNumberInfValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, $feedData, int $frequency, $value)
    {
        $feedData = \is_array($feedData) ? $feedData : [$feedData];

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('COUNT(d.date) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->andWhere('d.value <= :value');
        $queryBuilder->setParameter('value', $value);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get last date value
     *
     * @param FeedData $feedData
     * @param string $frequency
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|null
     */
    public function getLastValue(FeedData $feedData, $frequency)
    {
        return $this
            ->createQueryBuilder('d')
            ->select('MAX(d.date) AS date')
            ->andWhere('d.feedData = :feedData')
            ->setParameter('feedData', $feedData->getId())
            ->andWhere('d.frequency = :frequency')
            ->setParameter('frequency', $frequency)
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Get value
     *
     * @param FeedData|FeedData[] $feedData
     */
    public function getValue(?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, $feedData, int $frequency)
    {
        $feedData = \is_array($feedData) ? $feedData : [$feedData];

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);

        return $queryBuilder
            ->addGroupBy('d.id')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get value
     */
    public function getDateValueArray(?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, FeedData $feedData, int $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);

        return $queryBuilder
            ->addGroupBy('d.id')
            ->select('d.value, d.date')
            ->indexBy('d', 'd.date')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get repartition
     *
     * @param FeedData|FeedData[] $feedData
     * @param string $repartitionType
     *  must be in DataController::YEAR_HORIZONTAL_REPARTITION, DataController::YEAR_VERTICAL_REPARTITION
     */
    public function getRepartitionValue(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, $feedData, string $axeX, string $axeY, int $frequency, string $repartitionType)
    {
        $feedData = \is_array($feedData) ? $feedData : [$feedData];

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('AVG(d.value) AS value, d.' . $axeX . ' AS axeX, d.' . $axeY . ' AS axeY');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.' . $axeX);
        $queryBuilder->addGroupBy('d.' . $axeY);

        // If this is a year repartition, we also group by year.
        if (\in_array($repartitionType, [DataController::YEAR_HORIZONTAL_REPARTITION, DataController::YEAR_VERTICAL_REPARTITION])) {
            $queryBuilder->addSelect('d.year AS year');
            $queryBuilder->addGroupBy('d.year');
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get sum of value group by frequency (day, weekDay, week, month, year)
     *
     * @param FeedData|FeedData[] $feedData
     */
    public function getSumValueGroupBy(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, $feedData, int $frequency, string $groupBy)
    {
        $feedData = \is_array($feedData) ? $feedData : [$feedData];

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('SUM(d.value) AS value, d.' . $groupBy . ' AS groupBy');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.' . $groupBy);

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Add condition on querybuild on:
     *    - dates
     *    - feedData
     *    - frequency
     *
     * @param FeedData|FeedData[] $feedData
     */
    public function betweenDateWithFeedDataAndFrequency(?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, $feedDatas, int $frequency, QueryBuilder &$queryBuilder)
    {
        $feedDatas = \is_array($feedDatas) ? $feedDatas : [$feedDatas];

        // Deal with date
        if ($startDate) {
            $startDate = DataValue::adaptToFrequency($startDate, $frequency);
            $queryBuilder
                ->andWhere('d.date >= :start')
                ->setParameter('start', $startDate)
            ;
        }
        if ($endDate) {
            $queryBuilder
                ->andWhere('d.date <= :end')
                ->setParameter('end', $endDate)
            ;
        }

        // Add condition on feedData
        $queryBuilder
            ->andWhere($queryBuilder->expr()->in('d.feedData', \array_map(function($feedData) {return $feedData->getId();}, $feedDatas)))
        ;

        // Add condition on frequency
        $queryBuilder
            ->andWhere('d.frequency = :frequency')
            ->setParameter('frequency', $frequency)
        ;
    }

    /**
     * Get date interval of data.
     *
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|null
     */
    public function getPeriodDataAmplitude(Place $place)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder
            ->select('MIN(d.date), MAX(d.date)')
            ->innerJoin('d.feedData', 'fd')
            ->innerJoin('fd.feed', 'f')
            ->innerJoin('f.places', 'p', 'WITH', 'p = :place')
            ->setParameter('place', $place)
            ->andWhere('d.frequency = :frequency')
            ->setParameter('frequency', 2)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
