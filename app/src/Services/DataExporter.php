<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\DataValue;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Repository\DataValueRepository;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\WriterMultiSheetsAbstract;

/**
 * Data exporter services.
 */
class DataExporter
{
    /** @var DataValueRepository */
    private $dataValueRepository;

    public function __construct(DataValueRepository $dataValueRepository)
    {
        $this->dataValueRepository = $dataValueRepository;
    }

    /**
     * Export data between 2 dates as .ods for a place
     *
     * @param string $destination no trailing slash !
     * @return string filename
     */
    final public function exportPlace(Place $place, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null, string $destination = null): string
    {
        if (!$from || !$to) {
            $maxDates = $this->dataValueRepository->getPeriodDataAmplitude($place);
            $from = $from ?? ($maxDates[1] ? \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $maxDates[1]) : new \DateTimeImmutable('last year'));
            $to = $to ?? ($maxDates[2] ? \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $maxDates[2]) : new \DateTimeImmutable('yesterday'));
        }

        $filename = \sprintf(
            '%s/aeneria-%s-%s-to-%s',
            $destination ?? \sys_get_temp_dir(),
            $place->getName(),
            $from->format('Ymd'),
            $to->format('Ymd')
        );

        $filename .= '.ods';

        if (!$feedDatas = $place->getFeedDatas()) {
            throw new \InvalidArgumentException(\sprintf("L'adresse %s n'a pas de données à exporter.", $place->getName()));
        }

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($filename);

        foreach (DataValue::getAllFrequencies() as $frequency) {
            $this->exportForFrequency($writer, $feedDatas, $frequency, $from, $to);
        }

        $writer->close();

        return $filename;
    }

    /**
     * @param FeedData[] $feedDatas
     */
    private function exportForFrequency(WriterMultiSheetsAbstract $writer, array $feedDatas, int $frequency, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $sheetName = DataValue::getFrequencyMachineName($frequency);
        $sheet = $writer->getCurrentSheet();
        $sheet->setName($sheetName);

        $values = [];
        $firstRow = ['DATE'];
        foreach ($feedDatas as $feedData) {
            $values[$feedData->getId()] = $this->dataValueRepository->getDateValueArray($from, $to, $feedData, $frequency);
            $firstRow[] = $feedData->getDataType();
        }

        $writer->addRow(WriterEntityFactory::createRowFromArray($firstRow));

        $currentDate = DataValue::adaptToFrequency($from, $frequency);
        while ($currentDate < $to) {
            $row = [$currentDate->format('d/m/Y H:i')];
            $stringDate = $currentDate->format('Y-m-d H:i:s');

            foreach ($values as $value) {
                $row[] = \array_key_exists($stringDate, $value) ? $value[$stringDate]['value'] : null;
            }
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));

            $currentDate = DataValue::increaseToNextFrequence($currentDate, $frequency);
        }

        $writer->addNewSheetAndMakeItCurrent();
    }
}
