<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\MeteringValue;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\EnedisDataConnectApi\Client\DataConnectClientInterface;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Model\FetchingError;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Enedis Data Hub Provider
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
class EnedisDataConnectProvider extends AbstractFeedDataProvider
{
    /** @var DataConnectClientInterface */
    private $dataConnect;
    /** @var SerializerInterface */
    private $serializer;

    /** @var Token */
    private $accessToken = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        DataConnectClientInterface $dataConnect,
        SerializerInterface $serializer,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->dataConnect = $dataConnect;
        $this->serializer = $serializer;

        parent::__construct(
            $entityManager,
            $feedRepository,
            $feedDataRepository,
            $dataValueRepository,
            $notificationService,
            $logger
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFetchStrategy(): string
    {
        return parent::FETCH_STRATEGY_ONE_BY_ONE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getParametersName(Feed $feed): array
    {
        return [
            'PDL' => 'Point de livraison',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function isAvailableDataDate(\DateTimeImmutable $date): bool
    {
        // Get yesterday datetime.
        $reference = new \DateTimeImmutable();
        $reference = $reference->sub(new \DateInterval('P1D'));

        return $reference->format("Y-m-d") >= $date->format("Y-m-d");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        $errors = [];

        foreach ($feeds as $feed) {
            if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
                throw new \InvalidArgumentException("Should be an array of EnedisDataConnect Feeds overhere !");
            }

            // In order to avoid to flood enedis API, if for some reason, a feed gets too many errors
            // while fetching data, we stop asking for data for it.
            // We also display a notification to warn user about this situation.
            if ($feed->hasToManyFetchError()) {
                $this->notificationService->handleTooManyFetchErrorsNotification($feed);

                continue;
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                $this->logger->debug("EnedisDataConnect - Start fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);

                if ($data = $this->fetchDataForFeed($date, $feed, $errors)) {
                    $this->persistData($date, $feed, $data);

                    $feed->resetFetchError();
                    $this->entityManager->persist($feed);
                    $this->entityManager->flush();

                    $this->logger->info("EnedisDataConnect - Data fetched", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);
                }
            }
        }

        return $errors;
    }

    /**
     * Get a URL to DataConnect consent page.
     */
    public function getConsentUrl(string $state): string
    {
        return $this->dataConnect
            ->getAuthorizeV1Client()
            ->getConsentPageUrl(
                'P12M',
                $state
            )
        ;
    }

    /**
     * Check enedis consent from code. And update/create
     * related feed.
     */
    public function handleConsentCallback(string $usagePointId, Place $place): void
    {
        $address = $this->dataConnect
            ->getCustomersV5Client()
            ->requestUsagePointAdresse(
                $this->getAccessToken(),
                $usagePointId
            )
        ;

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_ELECTRICITY);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT);
            $place->addFeed($feed);
        }

        $feed->setName((string) $address);
        $feed->setSingleParam('ADDRESS', $this->serializer->serialize($address, 'json'));
        $feed->setSingleParam('PDL', $usagePointId);
        $feed->setFetchError(0);

        $this->entityManager->persist($feed);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $this->feedRepository->createDependentFeedData($feed);
        $this->entityManager->flush();
    }

    /**
     * Check enedis consent for a feed by trying
     * to get address informations.
     */
    public function consentCheck(Feed $feed): ?Address
    {
        if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Should be an array of EnedisDataConnect Feeds overhere !");
        }

        try {
            return $this->dataConnect
                ->getCustomersV5Client()
                ->requestUsagePointAdresse(
                    $this->getAccessToken(),
                    $this->getPdl($feed)
                )
            ;
        } catch (DataConnectException $e) {
            return null;
        }
    }

    private function fetchDataForFeed(\DateTimeImmutable $date, Feed $feed, array &$errors): array
    {
        $data = [];

        try {
            $data['days'] = $this->getDailyData($date, $feed);
        } catch (DataConnectException $e) {
            $this->logger->error("EnedisDataConnect - Error while fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d'), 'exception' => $e->getMessage()]);
            $errors[] = new FetchingError($feed, $date, $e);
        }

        try {
            $data['hours'] = $this->getHourlyData($date, $feed);
        } catch (DataConnectException $e) {
            $this->logger->error("EnedisDataConnect - Error while fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d'), 'exception' => $e->getMessage()]);
            $errors[] = new FetchingError($feed, $date, $e);
        }

        return $data;
    }

    private function getHourlyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $endDate = $date->add(new \DateInterval('P1D'));
        $startDate = clone $date;

        $meteringData = $this
            ->dataConnect
            ->getMeteringDataV5Client()
            ->requestConsumptionLoadCurve(
                $this->getAccessToken(),
                $this->getPdl($feed),
                $startDate,
                $endDate
            )
        ;

        // Data can be with PT30M PT10M and PT60M, we reconstitute a PT60M interval dataset
        $data = [];
        foreach ($meteringData->values as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            $key = $meteringValue
                ->date
                ->format('Y-m-d H:00')
            ;
            if (\array_key_exists($key, $data)) {
                $data[$key] += $meteringValue->value / 1000;
            } else {
                $data[$key] = $meteringValue->value / 1000;
            }
        }

        return $data;
    }

    private function getDailyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $endDate = $date->add(new \DateInterval('P1D'));
        $startDate = clone $date;

        $meteringData = $this
            ->dataConnect
            ->getMeteringDataV5Client()
            ->requestDailyConsumption(
                $this->getAccessToken(),
                $this->getPdl($feed),
                $startDate,
                $endDate
            )
        ;

        $data = [];
        foreach ($meteringData->values as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            if (\array_key_exists($key = $meteringValue->date->format('Y-m-d'), $data)) {
                $data[$key] += $meteringValue->value / 1000;
            } else {
                $data[$key] = $meteringValue->value / 1000;
            }
        }

        return $data;
    }

    /**
     * Persist data in database.
     */
    private function persistData(\DateTimeImmutable $date, Feed $feed, array $data)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneBy([
            'feed' => $feed->getId(),
            'dataType' => FeedData::FEED_DATA_CONSO_ELEC,
        ]);

        if (!$feedData) {
            throw new \Doctrine\ORM\EntityNotFoundException(\sprintf(
                "Could not find feedData of type %s for feed %s.",
                FeedData::FEED_DATA_CONSO_ELEC,
                $feed->getId()
            ));
        }

        // Persist hours data.
        foreach ($data['hours'] as $currentDate => $value) {
            if (isset($value) && -1 !== (int) $value) {
                $this->dataValueRepository->updateOrCreateValue(
                    $feedData,
                    \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $currentDate),
                    DataValue::FREQUENCY_HOUR,
                    (string) $value
                );
            }
        }

        // Persist day data.
        foreach ($data['days'] as $currentDate => $value) {
            if (isset($value) && -1 !== (int) $value) {
                $this->dataValueRepository->updateOrCreateValue(
                    $feedData,
                    \DateTimeImmutable::createFromFormat('!Y-m-d', $currentDate),
                    DataValue::FREQUENCY_DAY,
                    (string) $value
                );
            }
        }

        // Flush all persisted DataValue.
        $this->entityManager->flush();

        // Persist week data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_WEEK);
        $this->entityManager->flush();

        // Persist month data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_MONTH);
        $this->entityManager->flush();

        // Persist year data.
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_YEAR);
        $this->entityManager->flush();
    }

    public function getAddressFrom(Feed $feed): ?Address
    {
        if (Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a enedisDataConnect feed.");
        }

        if (\array_key_exists('ADDRESS', $feed->getParam()) && $address = $feed->getParam()['ADDRESS']) {
            $address = $this->serializer->deserialize($address, Address::class, 'json');
            \assert($address instanceof Address);

            return $address;
        }

        return null;
    }

    public function getPdl(Feed $feed): ?string
    {
        if (Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a enedisDataConnect feed.");
        }

        return $feed->getParam()['PDL'] ?? null;
    }

    /**
     * Get a valid Access Token
     */
    private function getAccessToken(): string
    {
        if (!$this->accessToken->isAccessTokenStillValid()) {
            $this->accessToken = $this
                ->dataConnect
                ->getAuthorizeV1Client()
                ->requestAuthorizationToken()
            ;

            // On évite de dépasser les quotas de Enedis,
            // on attends 1 seconde entre chaque requête
            \sleep(1);
        }

        return $this->accessToken->accessToken;
    }
}
