<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Tests\AppTestCase;

final class UserRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $this->getUserRepository();

        $user = $this->createPersistedUser();
        $entityManager->flush();
        $entityManager->clear();

        $userFromRepo = $userRepository->find($user->getId());

        self::assertSame($user->getId(), $userFromRepo->getId());
    }

    public function testUserList()
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $this->getUserRepository();

        $user = $this->createPersistedUser([
            'username' => $username = 'toto' . \rand(),
        ]);
        $entityManager->flush();
        $entityManager->clear();

        $userList = $userRepository->getUsersList();

        self::assertSame($userList[$username], $user->getId());
    }

    public function testisLastAdmin()
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $this->getUserRepository();

        $user = $this->createPersistedUser(['roles' => [User::ROLE_ADMIN]]);
        $this->createPersistedUser(['roles' => [User::ROLE_ADMIN]]);
        $entityManager->flush();
        $entityManager->clear();

        self::assertFalse($userRepository->isLastAdmin($user->getUsername()));
    }

    public function testPurge()
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $this->getUserRepository();

        $user = $this->createPersistedUser();
        $entityManager->flush();
        $entityManager->clear();

        $userRepository->purge($user);

        self::assertNull($userRepository->find($user->getId()));
    }
}