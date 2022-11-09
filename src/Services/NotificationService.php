<?php

namespace App\Services;

use App\Entity\Feed;
use App\Entity\Notification;
use App\Entity\Place;
use App\Entity\User;
use App\Model\FetchingError;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Notification services.
 */
class NotificationService
{
    /** @var NotificationRepository */
    private $notificationRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        LoggerInterface $logger
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function handleImportNotification(User $user, Place $place, ?array $errors): Notification
    {
        if (!$errors) {
            return $this->createNotification(
                $user,
                $place,
                Notification::LEVEL_SUCCESS,
                Notification::TYPE_DATA_IMPORT,
                "L'import du fichier de données a été réalisé avec succès !"
            );
        } else {
            $message = "Il y a eu une ou des erreurs lors de l'import du fichier de données :<br><ul>";

            foreach ($errors as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= '</ul>';

            return $this->createNotification($user, $place, Notification::LEVEL_ERROR, Notification::TYPE_DATA_IMPORT, $message);
        }
    }

    public function handleFetchDataNotification(User $user, Feed $feed, ?array $errors): ?Notification
    {
        if (!$place = $feed->getFirstPlace()) {
            $this->logger->error(\sprintf("Le feed %s n'est relié à aucune place !", $feed->getId()));

            return null;
        }

        if (!$errors) {
            return $this->createNotification(
                $user,
                $place,
                Notification::LEVEL_SUCCESS,
                Notification::TYPE_DATA_FETCH,
                "L'import du fichier de données a été réalisé avec succès !"
            );
        } else {
            $existing = $this->notificationRepository->findBy([
                'user' => $user,
                'level' => Notification::LEVEL_ERROR,
                'type' => Notification::TYPE_DATA_FETCH,
                'place' => $place,
            ]);

            // Pour le fetching de data, on ne créée pas une notification à chaque fois,
            // Sinon l'utilisateur va être inondé de notification
            if ($existing) {
                return null;
            }

            $message = \sprintf(
                "Toutes les données %s n'ont pas été correctement chargées :<br><ul>",
                \ucfirst($feed->getName())
            );

            foreach ($errors as $error) {
                \assert($error instanceof FetchingError);

                $message .= \sprintf(
                    "<li>Il y a eu une erreur pour %s pour la date du %s : '%s'</li>",
                    \ucfirst($error->getFeed()->getName()),
                    $error->getDate()->format('d/m/Y'),
                    $error->getException()->getMessage()
                );
            }
            $message .= '</ul>';

            return $this->createNotification($user, $place, Notification::LEVEL_ERROR, Notification::TYPE_DATA_FETCH, $message);
        }
    }

    public function handleTooManyFetchErrorsNotification(Feed $feed): ?Notification
    {
        if (!$place = $feed->getFirstPlace()) {
            $this->logger->error(\sprintf("Le feed %s n'est relié à aucune place !", $feed->getId()));

            return null;
        }

        $user = $place->getUser();

        $existing = $this->notificationRepository->findBy([
            'user' => $user,
            'level' => Notification::LEVEL_ERROR,
            'type' => Notification::TYPE_TOO_MANY_FETCH_ERROR,
            'place' => $place,
        ]);

        // Pour les erreurs de fetching, on ne créée pas une notification à chaque fois,
        // Sinon l'utilisateur va être inondé de notification
        if ($existing) {
            return null;
        }

        $message = \sprintf(
            <<<TXT
            <p>Il semble qu'il y ait eu des erreurs au moment de récupérer les données pour votre compteur %s.</p>
            <p>Il est possible que le consentement soit arrivé à échéance. Essayez de le renouveller via la
            page de configuration.</p>
            <p>Si le problème persiste, merci de contacter le support.</p>
            TXT,
            Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT == $feed->getFeedDataProviderType() ? 'Linky' : 'Gazpar'
        );

        return $this->createNotification($user, $place, Notification::LEVEL_ERROR, Notification::TYPE_TOO_MANY_FETCH_ERROR, $message);
    }

    private function createNotification(
        User $user,
        Place $place = null,
        string $level,
        string $type,
        string $message
    ): Notification {
        $notification = (new Notification())
            ->setUser($user)
            ->setPlace($place)
            ->setLevel($level)
            ->setType($type)
            ->setDate(new \DateTimeImmutable())
            ->setMessage($message)
        ;

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->logger->info('Notification créée', ['notifiaction' => $notification]);

        return $notification;
    }

    /**
     * @return Notification[]
     */
    public function getAndDeleteNotificationFor(User $user): ?iterable
    {
        $notifications = $this->notificationRepository->findNotificationForUser($user);

        if (\count($notifications)) {
            $this->deleteNotification($notifications);
        }

        return $notifications;
    }

    private function deleteNotification(array $notifications): void
    {
        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }
        $this->entityManager->flush();
    }
}
