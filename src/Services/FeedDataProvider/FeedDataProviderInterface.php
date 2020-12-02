<?php

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;

interface FeedDataProviderInterface
{
    /**
     * Fetch data for $date and for a array of feeds
     *
     * @param \Datetime $date
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): void;

    /**
     * Get array parameters that a feed which uses this provider should have.
     */
    public static function getParametersName(Feed $feed): array;

    /**
     * Fetch data from last data to $date.
     */
    public function fetchDataUntilLastUpdateTo(\DateTimeImmutable $date, array $feeds): void;

    /**
     * Fetch data for $date,
     * if $force is set to true, update data even if there are already ones.
     */
    public function fetchDataFor(\DateTimeImmutable $date, array $feeds, bool $force): void;

    /**
     * Fetch data from startDate to $endDate,
     * if $force is set to true, update data even if there are already ones.
     */
    public function fetchDataBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $feeds, bool $force): void;
}
