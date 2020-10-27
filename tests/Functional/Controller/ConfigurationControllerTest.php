<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class ConfigurationControllerTest extends AppWebTestCase
{
    public function testUserCanVisitConfigPages()
    {
        $this->login('user-test@example.com');

        $this->client->request('GET', "/configuration");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanVisitConfigPages()
    {
        $this->login('admin@example.com');

        $this->client->request('GET', "/configuration");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanVisitDeleteAccountPages()
    {
        $this->login('user-test@example.com');

        $this->client->request('GET', "/configuration/user/delete");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanUpdateProfil()
    {
        $this->login('user-test@example.com');

        $crawler = $this->client->request('GET', "/configuration/user/update");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['update_account'] = [
            'username' => 'user-test@example.com',
            'old_password' => 'password',
            'new_password' => 'password',
            'new_password2' => 'password',
        ];

        $crawler = $this->client->submit($form);

        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantUpdateProfilWithErrorOnPassword()
    {
        $this->login('user-test@example.com');

        $crawler = $this->client->request('GET', "/configuration/user/update");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['update_account'] = [
            'username' => 'user-test@example.com',
            'old_password' => 'password',
            'new_password' => 'password',
            'new_password2' => 'password2',
        ];

        $crawler = $this->client->submit($form);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantUpdateProfilWithErrorOnEmail()
    {
        $this->login('user-test@example.com');

        $crawler = $this->client->request('GET', "/configuration/user/update");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['update_account'] = [
            'username' => 'user-test@example.com',
            'old_password' => 'password',
            'new_password' => 'password',
            'new_password2' => 'password2',
        ];

        $crawler = $this->client->submit($form);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
