<?php
namespace AppBundle\FeedObject;

interface FeedObject {
    /**
     * Fetch data for $date
     *
     * @param \Datetime $date
     */
    public function fetchData(\Datetime $date);

    /**
     * Get frequencies for this type of feed.
     *
     * @param \Datetime $date
     * @return array
     */
    public static function getFrequencies();
}
