<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

abstract class AppWebTestCase extends WebTestCase
{
    use AppTestTrait;

    private $_container;
    protected $client;

    protected function setUp(): void
    {
        parent::setup();

        $this->client = static::createClient();
    }

    /**
     * Get container
     */
    final protected function getContainer(): ContainerInterface
    {
        return $this->_container ?? (
            $this->_container = $this->client->getContainer()
        );
    }

    final protected function login($username): User
    {
        $user = $this->getUserRepository()->findOneByUsername($username);
        $session = $this->getContainer()->get('session');

        $token = new PostAuthenticationGuardToken($user, 'main', $user->getRoles());
        $session->set('_security_main', \serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $user;
    }
}