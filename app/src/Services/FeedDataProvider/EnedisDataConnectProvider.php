<?php

namespace App\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\MeteringValue;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\EnedisDataConnectApi\Service\DataConnectServiceInterface;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Model\FetchingError;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\Migrations\Query\Exception\InvalidArguments;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Enedis Data Hub Provider
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
class EnedisDataConnectProvider extends AbstractFeedDataProvider
{
    /** @var bool */
    private $useProxyForEnedis;

    /** @var RouterInterface */
    private $router;
    /** @var DataConnectServiceInterface */
    private $dataConnect;
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        bool $useProxyForEnedis,
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        DataConnectServiceInterface $dataConnect,
        RouterInterface $router,
        SerializerInterface $serializer,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->useProxyForEnedis = $useProxyForEnedis;

        $this->router = $router;
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
            'TOKEN' => 'Objet Token',
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
        $url = $this->dataConnect
            ->getAuthorizeV1Service()
            ->getConsentPageUrl(
                'P12M',
                $state
            )
        ;

        // Adding callback url for aeneria proxy
        if ($this->useProxyForEnedis) {
            $url .= '&callback=';
            $url .= \urlencode(
                $this->router->generate('api.feed.enedis.consent.callback', [], RouterInterface::ABSOLUTE_URL)
            );
        }

        return $url;
    }

    /**
     * Check enedis consent from code.
     *
     * (Used in consentement process only)
     *
     * @return Array<Token|Address> [$token, $address]
     */
    public function consentCheckFromCode(string $code): array
    {
        $accessToken = $this->dataConnect
            ->getAuthorizeV1Service()
            ->requestTokenFromCode($code)
        ;

        $address = $this->dataConnect
            ->getCustomersService()
            ->requestUsagePointAdresse(
                $accessToken->getAccessToken(),
                $accessToken->getUsagePointsId()
            )
        ;

        return [$accessToken, $address];
    }

    /**
     * Check enedis consent from code. And update/create
     * related feed.
     */
    public function handleConsentCallback(string $code, Place $place): void
    {
        $accessToken = $this->dataConnect
            ->getAuthorizeV1Service()
            ->requestTokenFromCode($code)
        ;

        $address = $this->dataConnect
            ->getCustomersService()
            ->requestUsagePointAdresse(
                $accessToken->getAccessToken(),
                $accessToken->getUsagePointsId()
            )
        ;

        if (!$feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY)) {
            $feed = new Feed();
            $feed->setFeedType(Feed::FEED_TYPE_ELECTRICITY);
            $feed->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT);
            $place->addFeed($feed);
        }

        $feed->setName((string) $address);
        $feed->setSingleParam('TOKEN', $this->serializer->serialize($accessToken, 'json'));
        $feed->setSingleParam('ADDRESS', $this->serializer->serialize($address, 'json'));
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
            $this->ensureAccessToken($feed);
        } catch (DataConnectException $e) {
            return null;
        }

        if (!$token = $this->getTokenFrom($feed)) {
            return null;
        }

        try {
            return $this->dataConnect
                ->getCustomersService()
                ->requestUsagePointAdresse(
                    $token->getAccessToken(),
                    $token->getUsagePointsId()
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
            $this->ensureAccessToken($feed);
        } catch (DataConnectException $e) {
            $this->logger->error("EnedisDataConnect - Error while fetching data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d'), 'exception' => $e->getMessage()]);
            $errors[] = new FetchingError($feed, $date, $e);

            return $data;
        }

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

    /**
     * Ensure Access Token is out of date and renew it if needed
     */
    private function ensureAccessToken(Feed $feed): void
    {
        if (!$token = $this->getTokenFrom($feed)) {
            throw new InvalidArguments(\sprintf("Feed %s has no token !", $feed->getId()));
        }

        if (!$token->isAccessTokenStillValid()) {
            $renewedToken = $this
                ->dataConnect
                ->getAuthorizeV1Service()
                ->requestTokenFromRefreshToken($token->getRefreshToken())
            ;
            $feed->setSingleParam('TOKEN', $this->serializer->serialize($renewedToken, 'json'));

            $this->entityManager->persist($feed);
            $this->entityManager->flush();
        }
    }

    private function getHourlyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $token = $this->getTokenFrom($feed);

        $endDate = $date->add(new \DateInterval('P1D'));
        $startDate = clone $date;

        $meteringData = $this
            ->dataConnect
            ->getMeteringDataV4Service()
            ->requestConsumptionLoadCurve(
                $token->getAccessToken(),
                $token->getUsagePointsId(),
                $startDate,
                $endDate
            )
        ;

        // Data can be with PT30M PT10M and PT60M, we reconstitute a PT60M interval dataset
        $data = [];
        foreach ($meteringData->getValues() as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            $key = $meteringValue
                ->getDate()
                ->format('Y-m-d H:00')
            ;
            if (\array_key_exists($key, $data)) {
                $data[$key] += $meteringValue->getValue() / 1000;
            } else {
                $data[$key] = $meteringValue->getValue() / 1000;
            }
        }

        return $data;
    }

    private function getDailyData(\DateTimeImmutable $date, Feed $feed): array
    {
        $token = $this->getTokenFrom($feed);

        $endDate = $date->add(new \DateInterval('P1D'));
        $startDate = clone $date;

        $meteringData = $this
            ->dataConnect
            ->getMeteringDataV4Service()
            ->requestDailyConsumption(
                $token->getAccessToken(),
                $token->getUsagePointsId(),
                $startDate,
                $endDate
            )
        ;

        $data = [];
        foreach ($meteringData->getValues() as $meteringValue) {
            \assert($meteringValue instanceof MeteringValue);
            if (\array_key_exists($key = $meteringValue->getDate()->format('Y-m-d'), $data)) {
                $data[$key] += $meteringValue->getValue() / 1000;
            } else {
                $data[$key] = $meteringValue->getValue() / 1000;
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
                    $value
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

    public function getTokenFrom(Feed $feed): ?Token
    {
        if (Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT !== $feed->getFeedDataProviderType()) {
            throw new \InvalidArgumentException("Given feed is not a enedisDataConnect feed.");
        }

        if (\array_key_exists('TOKEN', $feed->getParam()) && $token = $feed->getParam()['TOKEN']) {
            $token = $this->serializer->deserialize($token, Token::class, 'json');
            \assert($token instanceof Token);

            return $token;
        }

        return null;
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
}
