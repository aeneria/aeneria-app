<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Tests\AppTestCase;

final class UserTest extends AppTestCase
{
    public function testUserInstance()
    {
        $user = $this->createUser([
            'username' => $username = 'test' . \rand(),
            'password' => $password = 'password' . \rand(),
            'active' => true,
            'roles' => [User::ROLE_USER],
        ]);

        self::assertSame($user->getUsername(), $username);
        self::assertSame($user->getPassword(), $password);
        self::assertSame($user->isActive(), true);
        self::assertSame($user->getRoles(), [User::ROLE_USER]);
        self::assertSame($user->isAdmin(), false);
    }

    public function testUserCanEditOwnPlace()
    {
        $place = $this->createPlace();
        $user = $this->createUser([
            'places' => [$place],
        ]);

        self::assertTrue($user->canEdit($place));
    }

    public function testUserCantEditSharedPlace()
    {
        $place = $this->createPlace();
        $place2 = $this->createPlace();
        $user = $this
            ->createUser([
                'sharedPlaces' => [$place],
            ])
            ->addSharedPlace($place2)
        ;

        self::assertTrue(!$user->canEdit($place));
        self::assertTrue(!$user->canEdit($place2));
    }

    public function testUserCantEditPublicPlace()
    {
        $place = $this->createPlace(['public' => true]);
        $user = $this->createUser();

        self::assertTrue(!$user->canEdit($place));
    }

    public function testUserCantEditUnknownPlace()
    {
        $place = $this->createPlace();
        $user = $this->createUser();

        self::assertTrue(!$user->canEdit($place));
    }

    public function testUserCanSeeOwnPlace()
    {
        $place = $this->createPlace();
        $user = $this->createUser([
            'places' => [$place],
        ]);

        self::assertTrue($user->canSee($place));
    }

    public function testUserCanSeeSharedPlace()
    {
        $place = $this->createPlace();
        $place2 = $this->createPlace();
        $user = $this
            ->createUser([
                'sharedPlaces' => [$place],
            ])
            ->addSharedPlace($place2)
        ;

        self::assertTrue($user->canSee($place));
        self::assertTrue($user->canSee($place2));
    }

    public function testUserCanSeePublicPlace()
    {
        $place = $this->createPlace(['public' => true]);
        $user = $this->createUser();

        self::assertTrue($user->canSee($place));
    }

    public function testUserCantSeeUnknownPlace()
    {
        $place = $this->createPlace();
        $user = $this->createUser();

        self::assertTrue(!$user->canSee($place));
    }
}
