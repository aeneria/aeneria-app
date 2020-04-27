<?php

namespace App\Tests\Functional\Controller;

use App\Tests\AppWebTestCase;

final class DataControllerTest extends AppWebTestCase
{
    public function testUserCanGetRepartition()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        foreach (['week', 'year_h', 'year_v'] as $repartitionType) {
            $this->client->request('GET', \sprintf(
                "/data/%s/repartition/conso_elec/%s/%s/%s",
                $places[0]->getId(),
                $repartitionType,
                (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
                (new \DateTimeImmutable('now'))->format("Y-m-d")
            ));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testUserCanGetEvolution()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/evolution/conso_elec/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetSumGroupBy()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/sum-group/conso_elec/day/weekDay/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetSum()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/sum/conso_elec/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetAverage()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/avg/conso_elec/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetMax()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/max/conso_elec/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetMin()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/min/conso_elec/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetNbInf()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/inf/conso_elec/2/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanGetXY()
    {
        $user = $this->login('user-test');
        $places = $user->getPlaces();

        $this->client->request('GET', \sprintf(
            "/data/%s/xy/conso_elec/dju/day/%s/%s",
            $places[0]->getId(),
            (new \DateTimeImmutable('7 days ago'))->format("Y-m-d"),
            (new \DateTimeImmutable('now'))->format("Y-m-d")
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
