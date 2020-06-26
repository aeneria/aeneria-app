<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class ConfigurationPlaceControllerTest extends AppWebTestCase
{
    public function testUserCanAddPlace()
    {
        $this->login('user-test');

        $this->client->request('GET', "/configuration/place/new");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanEditPlace()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $crawler = $this->client->request('GET', \sprintf("/configuration/place/%s/edit", $places[0]->getId()));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Enregistrer')->form();
        $crawler = $this->client->submit($form);

        $statutCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(\in_array($statutCode, [200, 302]));
    }

    public function testUserCanFetchDataForPlace()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $crawler = $this->client->request('GET', \sprintf("/configuration/place/%s/fetch", $places[0]->getId()));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $feeds = $places[0]->getFeeds();
        $form = $crawler->filter('form[name=' . $feeds[0]->getId() . ']')->selectButton('')->form();

        $form[$feeds[0]->getId() . '[start_date_' . $feeds[0]->getId() . ']'] = (new \DateTimeImmutable('now'))->format('d/m/Y');
        $form[$feeds[0]->getId() . '[end_date_' . $feeds[0]->getId() . ']'] = (new \DateTimeImmutable('now'))->format('d/m/Y');

        $crawler = $this->client->submit($form);

        $statutCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(\in_array($statutCode, [200, 302]));
    }

    public function testUserCanExportDataForPlace()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $crawler = $this->client->request('GET', \sprintf("/configuration/place/%s/export", $places[0]->getId()));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('')->form();
        $form['form[start_date]'] = (new \DateTimeImmutable('now'))->format('d/m/Y');
        $form['form[end_date]'] = (new \DateTimeImmutable('now'))->format('d/m/Y');
        $crawler = $this->client->submit($form);

        $statutCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(\in_array($statutCode, [200, 302]));
    }

    public function testUserCanImportDataForPlace()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $crawler = $this->client->request('GET', \sprintf("/configuration/place/%s/import", $places[0]->getId()));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('')->form();
        $form['form[file]']->upload($this->getResourceDir() . '/clean-export.ods');
        $crawler = $this->client->submit($form);

        $statutCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(\in_array($statutCode, [200, 302]));
    }
}
