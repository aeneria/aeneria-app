<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Repository\DataValueRepository;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Data importer services.
 */
class DataImporter
{
    public function __construct(
        private DataValueRepository $dataValueRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function importFile(Place $place, ?Feed $feed, string $filename): array
    {
        if (null === $feed) {
            return $this->importPlaceFile($place, $filename);
        }

        if (Feed::FEED_TYPE_ELECTRICITY === $feed->getFeedType()) {
            return $this->importEnedisFile($place, $feed, $filename);
        }

        if (Feed::FEED_TYPE_GAZ === $feed->getFeedType()) {
            return $this->importGrdfFile($place, $feed, $filename);
        }

        throw new \LogicException("No option found with given arguments.");
    }

    /**
     * Import data for a place from a .ods file
     */
    private function importPlaceFile(Place $place, string $filename): array
    {
        $this->logger->debug("Import Data - Start processing Import", ['place' => $place->getId(), 'filename' => $filename]);
        $errors = [];

        if ('application/vnd.oasis.opendocument.spreadsheet' !== \mime_content_type($filename)) {
            $errors[] = $message = \sprintf("Le fichier fourni n'est pas un ODS");
            return $errors;
        }

        $reader = ReaderEntityFactory::createODSReader();
        $reader->open($filename);

        foreach ($reader->getSheetIterator() as $sheet) {
            $errors = \array_merge($errors, $this->importPlaceFileSheet($sheet, $place));
        }

        $reader->close();
        $this->logger->info("Import Data - data imported.", ['place' => $place->getId(), 'filename' => $filename]);

        return $errors;
    }

    private function importPlaceFileSheet(SheetInterface $sheet, Place $place): array
    {
        // Ensure FeedDataType exists
        $frequencyMachineName = $sheet->getName();
        try {
            $frequency = DataValue::getFrequencyFromMachineName($frequencyMachineName);
        } catch (\InvalidArgumentException $e) {
            $frequencies = \implode(', ', \array_keys(DataValue::getAllFrequencies()));

            return [\sprintf(
                "La fréquence '%s' n'existe pas. Les fréquences acceptées sont : %s. Vérifiez le fichier importé.",
                $frequencyMachineName,
                $frequencies,
            )];
        }

        $dataValues = $errors = [];
        $from = $to = null;

        if (!$feedDatas = $place->getFeedDatas()) {
            throw new \InvalidArgumentException(\sprintf(
                "L'adresse '%s' n'a pas de flux (?!)",
                $place->getName()
            ));
        }

        $firstRow = [];
        foreach ($sheet->getRowIterator() as $number => $row) {
            \assert($row instanceof Row);

            if (!$firstRow) {
                foreach ($row->getCells() as $key => $cell) {
                    if (\array_key_exists($cell->getValue(), $feedDatas)) {
                        $firstRow[$key] = $feedDatas[$cell->getValue()];
                    }
                }
                continue;
            }

            $cells = $row->getCells();

            if (false === $date = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $cells[0]->getValue())) {
                $errors[] = $message = \sprintf("%s - ligne %s - La date n'est pas valide.", $frequencyMachineName, $number);
                $this->logger->error("Import Data - " . $message, ['place' => $place->getId()]);

                continue;
            }

            if (!$from) {
                $from = $date;
            }

            foreach ($firstRow as $key => $feedData) {
                if ('' !== $cells[$key]->getValue()) {
                    if (!\is_numeric($value = $cells[$key]->getValue())) {
                        $errors[] = $message = \sprintf(
                            "Feuille %s - ligne %s - La colone %s n'est pas valide.",
                            $frequencyMachineName,
                            $number,
                            $feedData->getDataType()
                        );

                        $this->logger->error("Import Data - " . $message, ['place' => $place->getId()]);
                        continue;
                    }

                    $dataValues[] = (new DataValue())
                        ->setFrequency($frequency)
                        ->setFeedData($feedData)
                        ->setDate($date)
                        ->setValue((float) $value)
                        ->updateDateRelatedData()
                    ;
                }
            }
        }

        if (\count($dataValues)) {
            $to = \end($dataValues)->getDate();
            $this->dataValueRepository->massImport(
                $from,
                \DateTimeImmutable::createFromInterface($to),
                $firstRow,
                $frequency,
                $dataValues
            );
        }

        return $errors;
    }

    /**
     * Import data for a place from a .xlsx GRDF file
     */
    private function importGrdfFile(Place $place, Feed $feed, string $filename): array
    {
        $feedData = $feed->getFeedData(FeedData::FEED_DATA_CONSO_GAZ);

        $this->logger->debug("Import Data - Start processing Import", ['place' => $place->getId(), 'feed' => $feed->getId(), 'filename' => $filename]);
        $errors = [];

        if ('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' !== \mime_content_type($filename)) {
            $errors[] = $message = \sprintf("Le fichier fourni n'est pas un XLSX");
            return $errors;
        }

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($filename);

        $start = $end = null;
        $batchStart = $batchEnd = null;
        $batchDataValues = [];
        $batchSize = 100;
        // Il n'y a qu'une feuille dans les fichiers GRDF
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $number => $row) {
                \assert($row instanceof Row);

                if ($number < 10) {
                    // Les 10 premières lignes contiennent des infos sur
                    // l'export, ça ne nous intéresse pas ici.
                    continue;
                }

                // Pour un import GRDF, le fichier contient toujour des données journalières.
                // On importe donc les données une à une e à chaque fois,
                // on met à jour les données aggrégées
                $cells = $row->getCells();

                if (false === $date = \DateTimeImmutable::createFromFormat('d/m/Y', $cells[1]->getValue())) {
                    $errors[] = $message = \sprintf("Ligne %s - La date n'est pas valide.", $number);
                    $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                    continue;
                }

                if (null === $start) {
                    $start = \DateTimeImmutable::createFromInterface($date);
                }
                $end = \DateTimeImmutable::createFromInterface($date);

                if (null === $batchStart) {
                    $batchStart = \DateTimeImmutable::createFromInterface($date);
                }
                $batchEnd = \DateTimeImmutable::createFromInterface($date);

                // C'est la 5e colonne qui contient les valeurs en kWh.
                if (!\is_numeric($value = $cells[5]->getValue())) {
                    $errors[] = $message = \sprintf(
                        "ligne %s - La colone 5 n'est pas valide.",
                        $number
                    );
                    $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                    continue;
                }

                $batchDataValues[] = (new DataValue())
                    ->setFrequency(DataValue::FREQUENCY_DAY)
                    ->setFeedData($feedData)
                    ->setDate($date)
                    ->setValue((float) $value)
                    ->updateDateRelatedData()
                ;

                if ($batchSize <= \count($batchDataValues)) {
                    $this->dataValueRepository->massImport($batchStart, $batchEnd, [$feedData], DataValue::FREQUENCY_DAY, $batchDataValues);
                    $batchStart = $batchEnd = null;
                    $batchDataValues = [];
                }
            }
        }

        $reader->close();

        $current = \DateTimeImmutable::createFromInterface($start);
        while($current < $end) {
            // Persist week data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_WEEK);
            $this->entityManager->flush();

            // Persist month data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_MONTH);
            $this->entityManager->flush();

            // Persist year data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_YEAR);
            $this->entityManager->flush();

            $current = $current->add(new \DateInterval('P1D'));
        }

        $this->logger->info("Import Data - data imported.", ['place' => $place->getId(), 'feed' => $feed->getId(), 'filename' => $filename]);

        return $errors;
    }

    /**
     * Import data for a place from a .csv Enedis file
     */
    private function importEnedisFile(Place $place, Feed $feed, string $filename): array
    {
        $feedData = $feed->getFeedData(FeedData::FEED_DATA_CONSO_ELEC);

        $this->logger->debug("Import Data - Start processing Import", ['place' => $place->getId(), 'feed' => $feed->getId(), 'filename' => $filename]);
        $errors = [];

        if (!$stream = \fopen($filename, "r")) {
            $errors[] = \sprintf("Le fichier n'a pas pu être ouvert");
            return $errors;
        }
        // La première ligne ne nous intéresse pas
        \fgetcsv($stream, 1024, ';');
        // La 2e ligne nous permet de savoir les données
        // sont journalière ou horaire.
        $info = \fgetcsv($stream, 1024, ';');
        $frequency = match ($info[1]) {
            'Courbe de charge' => DataValue::FREQUENCY_HOUR,
            'Index' => DataValue::FREQUENCY_DAY,
            default => null
        };

        if (!$frequency) {
            $errors[] = $message = \sprintf("Il manque des informations dans le fichier, provient-il bien d'Enedis ?");
            $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feed' => $feed->getId(), 'filename' => $filename]);

            return $errors;
        }

        // La 3e ligne ne nous intéresse pas
        \fgetcsv($stream, 1024, ';');

        switch ($frequency) {
            case DataValue::FREQUENCY_HOUR:
                $errors = \array_merge($errors, $this->importEnedisHourFile($place, $feed, $feedData, $stream));
                break;
            case DataValue::FREQUENCY_DAY:
                $errors = \array_merge($errors, $this->importEnedisDayFile($place, $feed, $feedData, $stream));
                break;
        }

        $this->logger->info("Import Data - data imported.", ['place' => $place->getId(), 'feed' => $feed->getId(), 'filename' => $filename]);

        return $errors;
    }

    private function importEnedisHourFile(
        Place $place,
        Feed $feed,
        FeedData $feedData,
        mixed $stream,
    ): array {
        $errors = [];

        $number = 0;
        $start = $end = null;
        $batchStart = $batchEnd = null;
        /** @var DataValue[] */
        $batchDataValues = [];
        $batchSize = 1000;
        while ($row = \fgetcsv($stream, 1024, ';')) {
            if (false === ($date = \DateTimeImmutable::createFromFormat('!Y-m-d?H+', $row[0]))) {
                $errors[] = $message = \sprintf("Ligne %s - La date n'est pas valide.", $number);
                $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                continue;
            }

            // C'est la 2e colonne qui contient les valeurs en kWh.
            if (!\is_numeric($value = $row[1])) {
                $errors[] = $message = \sprintf(
                    "ligne %s - La colone 2 n'est pas valides ou vides.",
                    $number
                );
                $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                continue;
            }

            if (null === $start) {
                $start = \DateTimeImmutable::createFromInterface($date);
            }
            $end = \DateTimeImmutable::createFromInterface($date);

            if (null === $batchStart) {
                $batchStart = \DateTimeImmutable::createFromInterface($date);
            }
            $batchEnd = \DateTimeImmutable::createFromInterface($date);

            $key = $date->format('Y-m-d H:00');

            if (\array_key_exists($key, $batchDataValues)) {
                $batchDataValues[$key]->setValue($batchDataValues[$key]->getValue() + (float)$value);
            } else {
                $batchDataValues[$key] = (new DataValue())
                    ->setFrequency(DataValue::FREQUENCY_HOUR)
                    ->setFeedData($feedData)
                    ->setDate($date)
                    ->setValue((float) $value / 1000) // Les données du fichier sont en Wh
                    ->updateDateRelatedData()
                ;
            }

            if ($batchSize <= \count($batchDataValues)) {
                $this->dataValueRepository->massImport($batchStart, $batchEnd, [$feedData], DataValue::FREQUENCY_HOUR, $batchDataValues);
                $batchStart = $batchEnd = null;
                $batchDataValues = [];
            }
        }
        \fclose($stream);

        $current = \DateTimeImmutable::createFromInterface($start);
        while($current < $end) {
            // Persist day data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_DAY);
            $this->entityManager->flush();

            // Persist week data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_WEEK);
            $this->entityManager->flush();

            // Persist month data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_MONTH);
            $this->entityManager->flush();

            // Persist year data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_YEAR);
            $this->entityManager->flush();

            $current = $current->add(new \DateInterval('P1D'));
        }

        return $errors;
    }

    private function importEnedisDayFile(
        Place $place,
        Feed $feed,
        FeedData $feedData,
        mixed $stream,
    ): array {
        $errors = [];

        $number = 0;
        $start = $end = null;
        $batchStart = $batchEnd = null;
        $batchDataValues = [];
        $batchSize = 1000;
        while ($row = \fgetcsv($stream, 1024, ';')) {
            // Le fichier contenant des données journalière finit par des lignes
            // décrivant la période couverte. ça ne nous intéresse pas ici.
            if ('Periode' === $row[0]) {
                break;
            }

            if (false === ($date = \DateTimeImmutable::createFromFormat('!Y-m-d?H+', $row[0]))) {
                $errors[] = $message = \sprintf("Ligne %s - La date n'est pas valide.", $number);
                $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                continue;
            }

            // C'est la 3e colonne qui contient les valeurs en kWh.
            if (!\is_numeric($value = $row[2])) {
                $errors[] = $message = \sprintf(
                    "ligne %s - La colone 3 n'est pas valides.",
                    $number
                );
                $this->logger->error("Import Data - " . $message, ['place' => $place->getId(), 'feedId' => $feed->getId()]);

                continue;
            }

            if (null === $start) {
                $start = \DateTimeImmutable::createFromInterface($date);
            }
            $end = \DateTimeImmutable::createFromInterface($date);

            if (null === $batchStart) {
                $batchStart = \DateTimeImmutable::createFromInterface($date);
            }
            $batchEnd = \DateTimeImmutable::createFromInterface($date);

            $batchDataValues[$date->format('Y-m-d H:00')] = (new DataValue())
                ->setFrequency(DataValue::FREQUENCY_HOUR)
                ->setFeedData($feedData)
                ->setDate($date)
                ->setValue((float) $value / 1000) // Les données du fichier sont en Wh
                ->updateDateRelatedData()
            ;

            if ($batchSize <= \count($batchDataValues)) {
                $this->dataValueRepository->massImport($batchStart, $batchEnd, [$feedData], DataValue::FREQUENCY_HOUR, $batchDataValues);
                $batchStart = $batchEnd = null;
                $batchDataValues = [];
            }
        }
        \fclose($stream);

        $current = \DateTimeImmutable::createFromInterface($start);
        while($current < $end) {
            // Persist week data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_WEEK);
            $this->entityManager->flush();

            // Persist month data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_MONTH);
            $this->entityManager->flush();

            // Persist year data.
            $this->dataValueRepository->updateOrCreateAgregateValue($current, $feed, DataValue::FREQUENCY_YEAR);
            $this->entityManager->flush();

            $current = $current->add(new \DateInterval('P1D'));
        }

        return $errors;
    }
}
