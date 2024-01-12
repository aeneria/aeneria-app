<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\Feed;
use App\Entity\Place;
use App\Entity\User;
use App\Services\DataImporter;
use App\Tests\AppTestCase;

final class DataImporterTest extends AppTestCase
{
    public function testDataImporterWithCleanExport()
    {
        $dataValueRepository = $this->getDataValueRepository();
        $entityManager = $this->getEntityManager();
        $dataImporter = new DataImporter(
            $dataValueRepository,
            $entityManager,
            $this->getLogger()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test@example.com');
        \assert($user instanceof User);

        $errors = $dataImporter->importFile(
            $user->getPlaces()[0],
            null,
            $this->getResourceDir() . '/clean-export.ods'
        );

        self::assertCount(0, $errors);
    }

    public function testDataImporterWithBrokenExport()
    {
        $dataValueRepository = $this->getDataValueRepository();
        $entityManager = $this->getEntityManager();
        $dataImporter = new DataImporter(
            $dataValueRepository,
            $entityManager,
            $this->getLogger()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test@example.com');
        \assert($user instanceof User);

        $errors = $dataImporter->importFile(
            $user->getPlaces()[0],
            null,
            $this->getResourceDir() . '/broken-export.ods'
        );

        // The file contains 4 errors
        self::assertCount(4, $errors);
    }

    public function testDataImporterEnedisHourFile()
    {
        $dataValueRepository = $this->getDataValueRepository();
        $entityManager = $this->getEntityManager();
        $dataImporter = new DataImporter(
            $dataValueRepository,
            $entityManager,
            $this->getLogger()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test@example.com');
        \assert($user instanceof User);

        $place = $user->getPlaces()[0];
        \assert($place instanceof Place);

        $feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);

        $errors = $dataImporter->importFile(
            $place,
            $feed,
            $this->getResourceDir() . '/Enedis_Conso_Heure.csv'
        );

        self::assertCount(0, $errors);
    }

    public function testDataImporterEnedisDayFile()
    {
        $dataValueRepository = $this->getDataValueRepository();
        $entityManager = $this->getEntityManager();
        $dataImporter = new DataImporter(
            $dataValueRepository,
            $entityManager,
            $this->getLogger()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test@example.com');
        \assert($user instanceof User);

        $place = $user->getPlaces()[0];
        \assert($place instanceof Place);

        $feed = $place->getFeed(Feed::FEED_TYPE_ELECTRICITY);

        $errors = $dataImporter->importFile(
            $place,
            $feed,
            $this->getResourceDir() . '/Enedis_Conso_Jour.csv'
        );

        self::assertCount(0, $errors);
    }

    public function testDataImporterGrdfFile()
    {
        $dataValueRepository = $this->getDataValueRepository();
        $entityManager = $this->getEntityManager();
        $dataImporter = new DataImporter(
            $dataValueRepository,
            $entityManager,
            $this->getLogger()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test@example.com');
        \assert($user instanceof User);

        $place = $user->getPlaces()[0];
        \assert($place instanceof Place);

        $feed = $place->getFeed(Feed::FEED_TYPE_GAZ);

        $errors = $dataImporter->importFile(
            $place,
            $feed,
            $this->getResourceDir() . '/export_grdf.xlsx'
        );

        self::assertCount(0, $errors);
    }
}
