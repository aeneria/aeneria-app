<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\GrdfAdict\Exception\GrdfAdictException;
use App\GrdfAdict\Model\InfoTechnique;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\GrdfAdict\Client\GrdfAdictAeneriaProxyClient;
use App\Model\FetchingError;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Grdf Adict Provider
 */
class ProxifiedGrdfAdictProvider extends AbstractFeedDataProvider
{
    /** @var GrdfAdictAeneriaProxyClient */
    private $grdfAdictProxy;
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        GrdfAdictAeneriaProxyClient $grdfAdictProxy,
        NotificationService $notificationService,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->grdfAdictProxy = $grdfAdictProxy;
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
            'PCE' => 'PCE du compteur Gazpar',
            'ENCODED_PCE' => 'PCE encodé, envoyé par le proxy au consentement',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function isAvailableDataDate(\DateTimeImmutable $date): bool
    {
        // Get yesterday datetime.
        $reference = new \DateTimeImmutable();
        $reference = $reference->sub(new \DateInterval('P2D'));

        return $reference->format("Y-m-d") >= $date->format("Y-m-d");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        $errors = [];

        foreach ($feeds as $feed) {
            if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED !== $feed->getFeedDataProviderType()) {
                throw new \InvalidArgumentException("Should be an array of Proxified GrdfAdict Feeds overhere !");
            }

            // In order to avoid to flood grdf API, if for some reason, a feed gets too many errors
            // while fetching data, we stop asking for data for it.
            // We also display a notification to warn user about this situation.
            if ($feed->hasToManyFetchError()) {
                $this->notificationService->handleTooManyFetchErrorsNotification($feed);

                continue;
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                $this->logger->debug("GrdfAdictProxified - Start fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);

                if ($data = $this->fetchDataForFeed($date, $feed, $errors)) {
                    $this->persistData($date, $feed, $data);

                    $feed->resetFetchError();
                    $this->entityManager->persist($feed);
                    $this->entityManager->flush();

                    $this->logger->info("GrdfAdictProxified - Data fetched", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);
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
        return $this->grdfAdictProxy->getConsentPageUrl($state);
    }

    /**
     * Check grdf consent from encodedPce. And update/create
     * related feed.
     */
    public function handleConsentCallback(string $encodedPce, Place $place): void
    {
        $info = $this->grdfAdictProxy->requestInfoTechnique($encodedPce);

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_GAZ)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_GAZ);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED);
            $place->addFeed($feed);
        }

        $feed->setName((string) $info);
        $feed->setFetchError(0);
        $feed->setSingleParam('PCE', $info->pce);
        $feed->setSingleParam('ENCODED_PCE', $encodedPce);
        $feed->setSingleParam('INFO', $this->serializer->serialize($info, 'json'));

        $this->entityManager->persist($feed);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        // Ensure all dependant FeedData are already existing
        $this->feedRepository->createDependentFeedData($feed);
        $this->entityManager->flush();
    }

    /**
     * Check Grdf consent for a feed by trying
     * to get address informations.
     */
    public function consentCheck(Feed $feed): ?InfoTechnique
    {
        if (Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Should be an array of GrdfAdict Feeds overhere !");
        }

        try {
            return $this
                ->grdfAdictProxy
                ->requestInfoTechnique($this->getEncodedPce($feed))
            ;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function fetchDataForFeed(\DateTimeImmutable $date, Feed $feed, array &$errors): array
    {
        $data = [];

        $endDate = $date->add(new \DateInterval('P1D'));
        $startDate = clone $date;

        try {
            $meteringData = $this
                ->grdfAdictProxy
                ->requestConsoInformative(
                    $this->getEncodedPce($feed),
                    $startDate,
                    $endDate
                )
            ;
        } catch (GrdfAdictException $e) {
            $this->logger->error("GrdfAdictProxified - Error while fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d'), 'exception' => $e->getMessage()]);
            $errors[] = new FetchingError($feed, $date, $e);

            return $data;
        }

        $key = $meteringData->date->format('Y-m-d');

        $data[$key] = $meteringData->value;

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
            'dataType' => FeedData::FEED_DATA_CONSO_GAZ,
        ]);

        if (!$feedData) {
            throw new \Doctrine\ORM\EntityNotFoundException(\sprintf(
                "Could not find feedData of type %s for feed %s.",
                FeedData::FEED_DATA_CONSO_GAZ,
                $feed->getId()
            ));
        }

        // Persist day data.
        foreach ($data as $currentDate => $value) {
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

    public function getPce(Feed $feed): ?string
    {
        if (Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a Proxfied GrdfAdict feed.");
        }

        return $feed->getParam()['PCE'] ?? null;
    }

    public function getEncodedPce(Feed $feed): ?string
    {
        if (Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a Proxfied GrdfAdict feed.");
        }

        return $feed->getParam()['ENCODED_PCE'] ?? null;
    }
}
