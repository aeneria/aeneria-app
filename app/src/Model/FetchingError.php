<?php

declare(strict_types=1);

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

    public function getFeed(): Feed
    {
        return $this->feed;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }
}
