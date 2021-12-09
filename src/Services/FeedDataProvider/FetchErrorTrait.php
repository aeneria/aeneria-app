<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;

trait FetchErrorTrait
{
    /**
     * Est-ce que le feed courant a déjà eu trop de problème lors des dernières
     * récupérations de données
     */
    protected function hasToManyFetchError(Feed $feed): bool
    {
        $nbConsentErrors = $feed->getSingleParam(AbstractFeedDataProvider::ERROR_CONSENT, 0);
        $nbFetchErrors = $feed->getSingleParam(AbstractFeedDataProvider::ERROR_FETCH, 0);

        return ($nbConsentErrors > 10) && ($nbFetchErrors > 100);
    }

    protected function resetFetchError(Feed $feed): void
    {
        $feed->setSingleParam(AbstractFeedDataProvider::ERROR_CONSENT, 0);
        $feed->setSingleParam(AbstractFeedDataProvider::ERROR_FETCH, 0);

        $this->entityManager->persist($feed);
        $this->entityManager->flush();
    }

    protected function logError(Feed $feed, string $type): void
    {
        if (!\in_array($type, [AbstractFeedDataProvider::ERROR_CONSENT, AbstractFeedDataProvider::ERROR_FETCH])) {
            throw new \InvalidArgumentException("Given error type is unknown.");
        }

        $feed->setSingleParam(
            $type,
            $feed->getSingleParam($type, 0) + 1
        );

        $this->entityManager->persist($feed);
        $this->entityManager->flush();
    }
}