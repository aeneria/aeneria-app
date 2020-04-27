<?php

namespace App\Services;

use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\WriterMultiSheetsAbstract;

/**
 * Data exporter services.
 */
class DataExporter
{
    private $feedDataRepository;
    private $dataValueRepository;

    public function __construct(FeedDataRepository $feedDataRepository, DataValueRepository $dataValueRepository)
    {
        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;
    }

    /**
     * Export data between 2 dates as .ods for a place
     *
     * @param string $destination no trailing slash !
     * @return string filename
     */
    final public function exportPlace(Place $place, \DateTimeImmutable $from, \DateTimeImmutable $to, string $destination = null): string
    {
        $filename = \sprintf(
            '%s/aeneria-%s-%s-to-%s',
            $destination ?? \sys_get_temp_dir(),
            $place->getName(),
            $from->format('Ymd'),
            $to->format('Ymd')
        );

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($filename);

        foreach ($place->getFeeds() as $feed) {
            $this->exportFeed($writer, $feed, $from, $to);
        }

        $writer->close();

        return $filename;
    }

    private function exportFeed(WriterMultiSheetsAbstract $writer, Feed $feed, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        foreach ($this->feedDataRepository->findBy(['feed' => $feed]) as $feedData) {
            $this->exporFeedData($writer, $feed, $feedData, $from, $to);
        }
    }

    private function exporFeedData(WriterMultiSheetsAbstract $writer, Feed $feed, FeedData $feedData, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $sheetName = $feedData->getDisplayDataType();
        $sheet = $writer->getCurrentSheet();
        $sheet->setName($sheetName);

        if ($values = $this->dataValueRepository->getValue(
            $from,
            $to,
            $feedData,
             \min($feed->getFrequencies())
        )) {
            foreach ($values as $value) {
                $row = WriterEntityFactory::createRowFromArray([
                    $value->getDate()->format('d/m/Y H:i'),
                    $value->getValue(),
                ]);
                $writer->addRow($row);
            }
        }

        $writer->addNewSheetAndMakeItCurrent();
    }
}
