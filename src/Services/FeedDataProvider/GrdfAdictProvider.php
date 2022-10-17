<?php

namespace App\Services\FeedDataProvider;

use Aeneria\GrdfAdictApi\Exception\GrdfAdictConsentException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictException;
use Aeneria\GrdfAdictApi\Model\InfoTechnique;
use Aeneria\GrdfAdictApi\Model\Token;
use Aeneria\GrdfAdictApi\Service\GrdfAdictServiceInterface;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Model\FetchingError;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Grdf Adict Provider
 */
class GrdfAdictProvider extends AbstractFeedDataProvider
{
    use FetchErrorTrait;

    /** @var GrdfAdictServiceInterface */
    private $grdfAdict;

    /** @var Token */
    private $accessToken = null;

    /** @var NotificationService */
    private $notificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        GrdfAdictServiceInterface $grdfAdict,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->grdfAdict = $grdfAdict;
        $this->notificationService = $notificationService;

        parent::__construct($entityManager, $feedRepository, $feedDataRepository, $dataValueRepository, $logger);
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        $errors = [];

        foreach ($feeds as $feed) {
            if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_GRDF_ADICT !== $feed->getFeedDataProviderType()) {
                throw new \InvalidArgumentException("Should be an array of GrdfAdict Feeds overhere !");
            }

            // In order to avoid to flood enedis API, if for some reason, a feed gets too many errors
            // while fetching data, we stop asking for data for it.
            // We also display a notification to warn user about this situation.
            if ($this->hasToManyFetchError($feed)) {
                $this->notificationService->handleTooManyFetchErrorsNotification($feed);

                continue;
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                $this->logger->debug("GrdfAdict - Start fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);

                if ($data = $this->fetchDataForFeed($date, $feed, $errors)) {
                    $this->persistData($date, $feed, $data);
                    $this->resetFetchError($feed);

                    $this->logger->info("GrdfAdict - Data fetched", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);
                }
            }
        }

        return $errors;
    }

    /**
     * Get a valid Access Token
     */
    private function getAccessToken(): string
    {
        if (!$this->accessToken || !$this->accessToken->isAccessTokenStillValid()) {
            $this->accessToken = $this
                ->grdfAdict
                ->getAuthentificationService()
                ->requestAuthorizationToken()
            ;

            // On évite de dépasser les quotas de GRDF,
            // on attends 1 seconde entre chaque requête
            \sleep(1);
        }

        return $this->accessToken->getAccessToken();
    }

    /**
     * Check enedis consent for a feed by trying
     * to get address informations.
     */
    public function consentCheck(Feed $feed): ?InfoTechnique
    {
        if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_GRDF_ADICT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Should be an array of GrdfAdict Feeds overhere !");
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (GrdfAdictException $e) {
            return null;
        }

        try {
            return $this->grdfAdict
                ->getContratService()
                ->requestInfoTechnique(
                    $accessToken,
                    $this->getPce($feed)
                )
            ;
        } catch (GrdfAdictException $e) {
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
                ->grdfAdict
                ->getConsommationService()
                ->requestConsoInformative(
                    $this->getAccessToken(),
                    $this->getPce($feed),
                    $startDate,
                    $endDate
                )
            ;

            // On évite de dépasser les quotas de GRDF,
            // on attends 1 seconde entre chaque requête
            \sleep(1);
        } catch (GrdfAdictException $e) {
            $this->logError(
                $feed,
                $e instanceof GrdfAdictConsentException ? self::ERROR_CONSENT : self::ERROR_FETCH
            );

            $this->logger->error("GrdfAdict - Error while fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d'), 'exception' => $e->getMessage()]);
            $errors[] = new FetchingError($feed, $date, $e);

            return $data;
        }

        $key = $meteringData->getDate()->format('Y-m-d');

        $data[$key] = $meteringData->getValue();

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
                    $value
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
        if (Feed::FEED_DATA_PROVIDER_GRDF_ADICT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a grdfAdict feed.");
        }

        return $feed->getParam()['PCE'] ?? null;
    }
}
