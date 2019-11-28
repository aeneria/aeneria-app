<?php
namespace App\FeedDataProvider;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractDataProvider {

    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Fetch data for $date
     *
     * @param \Datetime $date
     */
    public function fetchData(\Datetime $date)
    {

    }

    /**
     * Get frequencies for this type of feed.
     *
     * @return array
     */
    public static function getFrequencies()
    {

    }
}
