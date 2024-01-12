<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

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
}
