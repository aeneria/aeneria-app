<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class SecurityControllerTest extends AppWebTestCase
{
    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Connexion')->form();
        $form['username'] = 'user-test@example.com';
        $form['password'] = 'password';

        $crawler = $this->client->submit($form);

        self::assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followredirect();
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testVisitLoginPageWhenAlreadyLoggued()
    {
        $this->login('user-test@example.com');

        $this->client->request('GET', '/login');
        self::assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followredirect();
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
