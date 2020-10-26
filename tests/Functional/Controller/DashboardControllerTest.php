<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class DashboardControllerTest extends AppWebTestCase
{
    public function urlsProvider()
    {
        return [
            ['/'],
            ['/electricity'],
            ['/meteo'],
            ['/energy_x_meteo'],
        ];
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testUserCanAccessDashboards($url)
    {
        $this->login('user-test@example.com');

        $this->client->request('GET', $url);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
