<?php

namespace App\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\MeteringValue;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\EnedisDataConnectApi\Service\DataConnectServiceInterface;
use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Enedis Data Hub Provider
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
class EnedisDataConnectProvider extends AbstractFeedDataProvider
{
    /** @var DataConnectServiceInterface */
    private $dataConnect;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        DataConnectServiceInterface $dataConnect,
        SerializerInterface $serializer
    ) {
        $this->dataConnect = $dataConnect;
        $this->serializer = $serializer;

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
                $this->ensureAccessToken($feed);
                $data['hours'] = $this->getHourlyData($date, $feed);
                $data['days'] = $this->getDailyData($date, $feed);
                $this->persistData($date, $feed, $data);
            }
        }
    }

    /**
     * Ensure Access Token is out of date and renew it if needed
     */
    private function ensureAccessToken(Feed $feed): void
    {
        $token = $this->getTokenFrom($feed);

        if (!$token->isAccessTokenStillValid()) {
            $renewedToken = $this
                ->dataConnect
                ->getAuthorizeV1Service()
                ->requestTokenFromRefreshToken($token->getAccessToken())
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
                $data[$key] += $meteringValue->getValue()/1000;
            } else {
                $data[$key] = $meteringValue->getValue()/1000;
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
                $data[$key] += $meteringValue->getValue()/1000;
            } else {
                $data[$key] = $meteringValue->getValue()/1000;
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
            if ($value && -1 !== (int) $value) {
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
            if ($value && -1 !== (int) $value) {
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
