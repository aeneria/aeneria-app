<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Services\DataExporter;
use App\Tests\AppTestCase;

final class DataExporterTest extends AppTestCase
{
    public function testDataExporter()
    {
        $dataExporter = new DataExporter(
            $this->getFeedDataRepository(),
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
}
