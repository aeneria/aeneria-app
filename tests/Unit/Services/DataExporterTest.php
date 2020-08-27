<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\User;
use App\Services\DataExporter;
use App\Tests\AppTestCase;

final class DataExporterTest extends AppTestCase
{
    public function testDataExporterWithDates()
    {
        $dataExporter = new DataExporter(
            $this->getDataValueRepository()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test');
        \assert($user instanceof User);

        $filename = $dataExporter->exportPlace(
            $user->getPlaces()[0],
            new \DateTimeImmutable('7 days ago'),
            new \DateTimeImmutable()
        );

        self::assertTrue(\file_exists($filename));
    }

    public function testDataExporterWithoutDates()
    {
        $dataExporter = new DataExporter(
            $this->getDataValueRepository()
        );

        $user = $this->getUserRepository()->findOneByUsername('user-test');
        \assert($user instanceof User);

        $filename = $dataExporter->exportPlace($user->getPlaces()[0]);

        self::assertTrue(\file_exists($filename));
    }
}
