<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class AdministrationControllerTest extends AppWebTestCase
{
    public function urlsProvider()
    {
        return [
            ['users'],
            ['users/add'],
            ['log'],
        ];
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testAdminCanAccessAdminPages($url)
    {
        $this->login('admin@example.com');

        $this->client->request('GET', \sprintf("/admin/%s", $url));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testUserCantAccessAdminPages($url)
    {
        $this->login('user-test@example.com');

        $this->client->request('GET', \sprintf("/admin/%s", $url));
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function userRelatedUrlsProvider()
    {
        return [
            ['update'],
            ['disable'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider userRelatedUrlsProvider
     */
    public function testAdminCanAccessAdminUserPages($url)
    {
        $user = $this->login('admin@example.com');

        $this->client->request('GET', \sprintf("/admin/users/%s/%s", $user->getId(), $url));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider userRelatedUrlsProvider
     */
    public function testUserCantAccessAdminUserPages($url)
    {
        $user = $this->login('user-test@example.com');

        $this->client->request('GET', \sprintf("/admin/users/%s/%s", $user->getId(), $url));
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
