<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;
use App\Model\FetchingError;

interface FeedDataProviderInterface
{
    /**
     * Fetch data for $date and for a array of feeds
     *
     * @return FetchingError[]
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array;

    /**
     * Get array parameters that a feed which uses this provider should have.
     */
    public static function getParametersName(Feed $feed): array;

    /**
     * Fetch data from last data to last available data.
     *
     * Each FeedDataProvider has to know what is
     * "last available data" for its own implementation.
     *
     * @return FetchingError[]
     */
    public function fetchDataUntilLastUpdateTo(array $feeds): array;

    /**
     * Fetch data for $date,
     * if $force is set to true, update data even if there are already ones.
     *
     * @return FetchingError[]
     */
    public function fetchDataFor(\DateTimeImmutable $date, array $feeds, bool $force): array;

    /**
     * Fetch data from startDate to $endDate,
     * if $force is set to true, update data even if there are already ones.
     *
     * @return FetchingError[]
     */
    public function fetchDataBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $feeds, bool $force): array;
}
