<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\DataValue;
use App\Entity\Place;
use App\Repository\DataValueRepository;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;
use Psr\Log\LoggerInterface;

/**
 * Data importer services.
 */
class DataImporter
{
    /** @var DataValueRepository */
    private $dataValueRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DataValueRepository $dataValueRepository,
        LoggerInterface $logger
    ) {
        $this->dataValueRepository = $dataValueRepository;
        $this->logger = $logger;
    }

    /**
     * Import data for a place from a .ods file
     */
    public function importPlace(Place $place, string $filename): array
    {
        $this->logger->debug("Import Data - Start processing Import", ['place' => $place->getId(), 'filename' => $filename]);
        $errors = [];

        if ('application/vnd.oasis.opendocument.spreadsheet' !== \mime_content_type($filename)) {
            throw new \InvalidArgumentException("Given file should be an ODS file");
        }

        $reader = ReaderEntityFactory::createODSReader();
        $reader->open($filename);

        foreach ($reader->getSheetIterator() as $sheet) {
            $errors = \array_merge($errors, $this->importSheet($sheet, $place));
        }

        $reader->close();
        $this->logger->info("Import Data - data imported.", ['place' => $place->getId(), 'filename' => $filename]);

        return $errors;
    }

    private function importSheet(SheetInterface $sheet, Place $place): array
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
                        )
                        ;

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
}
