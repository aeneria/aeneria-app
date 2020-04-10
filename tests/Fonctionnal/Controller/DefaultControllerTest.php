<?php

namespace App\Tests\Fonctionnal\Controller;

use App\Tests\AppWebTestCase;

final class DefaultControllerTest extends AppWebTestCase
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
        $this->login('user-test');

        $this->client->request('GET', $url);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
