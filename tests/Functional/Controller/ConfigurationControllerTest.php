<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class ConfigurationControllerTest extends AppWebTestCase
{
    public function configUrlsProvider()
    {
        return [
            [''],
            ['/user/update'],
            ['/user/delete'],
        ];
    }

    /**
     * @dataProvider configUrlsProvider
     */
    public function testUserCanVisitConfigPages($url)
    {
        $this->login('user-test');

        $this->client->request('GET', "/configuration" . $url);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanAddPlace()
    {
        $this->login('user-test');

        $this->client->request('GET', "/configuration/place/add");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function placeRelatedUrlsProvider()
    {
        return [
            ['update'],
            ['delete'],
            ['fetch'],
            ['export'],
        ];
    }

    /**
     * @dataProvider placeRelatedUrlsProvider
     */
    public function testUserCanVisitPlaceRelatedPages($url)
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf("/configuration/place/%s/%s", $places[0]->getId(), $url));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
