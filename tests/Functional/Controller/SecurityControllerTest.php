<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class SecurityControllerTest extends AppWebTestCase
{
    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Connexion')->form();
        $form['username'] = 'user-test';
        $form['password'] = 'password';

        $crawler = $this->client->submit($form);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followredirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testVisitLoginPageWhenAlreadyLoggued()
    {
        $this->login('user-test');

        $this->client->request('GET', '/login');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followredirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
