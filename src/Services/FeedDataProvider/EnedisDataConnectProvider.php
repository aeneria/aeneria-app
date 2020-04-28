<?php

namespace App\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\MeteringValue;
use Aeneria\EnedisDataConnectApi\Services\DataConnectService;
use Aeneria\EnedisDataConnectApi\Token;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Enedis Data Hub Provider
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
class EnedisDataConnectProvider extends AbstractFeedDataProvider
{
    /** @var DataConnectService */
    private $dataConnect;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        DataConnectService $dataConnect
    ) {
        $this->dataConnect = $dataConnect;

        parent::__construct($entityManager, $feedRepository, $feedDataRepository, $dataValueRepository);
    }

    /**
     * {@inheritdoc}
     */
    public static function getParametersName(Feed $feed): array
    {
        return [
            'TOKEN' => 'Objet Token',
        ];
    }

    /**
     * Fetch ENEDIS data for $date and persist its in database.
     *
     * @param \DateTime $date
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false)
    {
        foreach ($feeds as $feed) {
            if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
                throw new \InvalidArgumentException("Should be an array of EnedisDataConnect Feeds overhere !");
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                if ($this->renewAccessToken($feed)) {
                    $data['hours'] = $this->getHourlyData($date, $feed);
                    $data['days'] = $this->getDailyData($date, $feed);
                    $this->persistData($date, $feed, $data);
                }
            }
        }
    }

    /**
     * Renew Access Token for a Feed
     */
    private function renewAccessToken(Feed $feed): bool
    {
        $token = $this->getTokenFrom($feed);

        $renewedToken = $this->dataConnect->requestTokenFromRefreshToken($token->getAccessToken());
        $feed->setParam(['TOKEN' => $renewedToken]);

        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        return (bool) $renewedToken->getAccessToken();
    }

    private function getHourlyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $token = $this->getTokenFrom($feed);

        $endDate = \DateTime::createFromImmutable($date);
        $endDate->add(new \DateInterval('P1D'));
        $startDate = \DateTime::createFromImmutable($date);
        $startDate->sub(new \DateInterval('P2D'));

        $meteringData = $this->dataConnect->requestConsumptionLoadCurve(
            $token->getUsagePointsId(),
            $startDate,
            $endDate,
            $token->getAccessToken()
        );

        // Data can be with PT30M PT10M and PT60M, we reconstitute a PT60M interval dataset
        $data = [];
        foreach( $meteringData->getValues() as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            $data[$meteringValue->getDate()->format('Y-m-d h:00')] += $meteringValue->getValue();
        }

        return $data;
    }

    private function getDailyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $token = $this->getTokenFrom($feed);

        $endDate = \DateTime::createFromImmutable($date);
        $endDate->add(new \DateInterval('P1D'));
        $startDate = \DateTime::createFromImmutable($date);
        $startDate->sub(new \DateInterval('P2D'));

        $meteringData = $this->dataConnect->requestDailyConsumption(
            $token->getUsagePointsId(),
            $startDate,
            $endDate,
            $token->getAccessToken()
        );

        $data = [];
        foreach( $meteringData->getValues() as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            $data[$meteringValue->getDate()->format('Y-m-d')] += $meteringValue->getValue();
        }

        return $data;
    }

    /**
     * Persist data in database.
     *
     * @param \DateTime $date
     */
    private function persistData(\DateTimeImmutable $date, Feed $feed, array $data)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);

        // Persist hours data.
        foreach ($data['hours'] as $date => $value) {
            if ($value && -1 !== (int) $value) {
                $this->dataValueRepository->updateOrCreateValue(
                    $feedData,
                    \DateTimeImmutable::createFromFormat('!Y-m-d h:m', $date),
                    DataValue::FREQUENCY['HOUR'],
                    $value
                );
            }
        }

        // Persist day data.
        foreach ($data['days'] as $date => $value) {
            if ($value && -1 !== (int) $value) {
                $this->dataValueRepository->updateOrCreateValue(
                    $feedData,
                    \DateTimeImmutable::createFromFormat('!Y-m-d', $date),
                    DataValue::FREQUENCY['DAY'],
                    $value
                );
            }
        }

        // Flush all persisted DataValue.
        $this->entityManager->flush();

        // Persist week data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY['WEEK']);
        $this->entityManager->flush();

        // Persist month data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY['MONTH']);
        $this->entityManager->flush();

        // Persist year data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY['YEAR']);
        $this->entityManager->flush();
    }

    private function getTokenFrom(Feed $feed): Token
    {
        $param = $feed->getParam();
        $token = $param['TOKEN'];
        \assert($token instanceof Token);

        return $token;
    }
}
