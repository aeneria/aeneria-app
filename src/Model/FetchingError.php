<?php

namespace App\Model;

use App\Entity\Feed;

class FetchingError
{
    /** @var Feed */
    private $feed;

    /** @var \DateTimeInterface */
    private $date;

    /** @var \Exception */
    private $exception;

    public function __construct(Feed $feed, \DateTimeInterface $date, \Exception $exception)
    {
        $this->feed = $feed;
        $this->date = $date;
        $this->exception = $exception;
    }


    /** @var Feed */
    public function getFeed(): Feed
    {
        return $this->feed;
    }

    /** @var \DateTimeInterface */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /** @var \Exception */
    public function getException(): \Exception
    {
        return $this->exception;
    }
}