<?php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

/**
 * DataValue
 */
class DataValue implements \JsonSerializable
{
    /** @deprecated use DataValue::getAllFrequencies() instead */
    const FREQUENCY = [
        'HOUR' => 1,
        'DAY' => 2,
        'WEEK' => 3,
        'MONTH' => 4,
        'YEAR' => 5,
    ];

    const FREQUENCY_HOUR = 1;
    const FREQUENCY_DAY = 2;
    const FREQUENCY_WEEK = 3;
    const FREQUENCY_MONTH = 4;
    const FREQUENCY_YEAR = 5;

    /** @var int */
    private $id;

    /** @var float */
    private $value;

    /** @var \DateTimeInterface */
    private $date;

    /** @var int */
    private $hour;

    /** @var int */
    private $weekDay;

    /** @var int */
    private $week;

    /** @var int */
    private $month;

    /** @var int */
    private $year;

    /** @var FeedData */
    private $feedData;

    /** @var int */
    private $frequency;

    public static function getAllFrequencies(): array
    {
        return [
            'HOUR' => DataValue::FREQUENCY_HOUR,
            'DAY' => DataValue::FREQUENCY_DAY,
            'WEEK' => DataValue::FREQUENCY_WEEK,
            'MONTH' => DataValue::FREQUENCY_MONTH,
            'YEAR' => DataValue::FREQUENCY_YEAR,
        ];
    }

    public static function getFrequencyMachineName(int $frequency): string
    {
        if (!\in_array($frequency, DataValue::getAllFrequencies())) {
            throw new \InvalidArgumentException(\sprintf(
                'La fréquence %s n\'existe pas',
                $frequency
            ));
        }

        return \array_search($frequency, DataValue::getAllFrequencies());
    }

    public static function getFrequencyFromMachineName(string $machineName): int
    {
        if (!\array_key_exists($machineName, DataValue::getAllFrequencies())) {
            throw new \InvalidArgumentException(\sprintf(
                'La fréquence %s n\'existe pas',
                $machineName
            ));
        }

        return DataValue::getAllFrequencies()[$machineName];
    }

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set value
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Set date
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Update hour, weekDay, week, month and year from current date & frequency
     */
    public function updateDateRelatedData(): self
    {
        if ($this->frequency <= DataValue::FREQUENCY_HOUR) {
            $this->setHour((int) $this->date->format('H'));
        }
        $weekDay = 0 == $this->date->format('w') ? 6 : $this->date->format('w') - 1;
        if ($this->frequency <= DataValue::FREQUENCY_DAY) {
            $this->setWeekDay($weekDay);
        }
        if ($this->frequency <= DataValue::FREQUENCY_WEEK) {
            $this->setWeek((int) $this->date->format('W'));
        }
        if ($this->frequency <= DataValue::FREQUENCY_MONTH) {
            $this->setMonth((int) $this->date->format('m'));
        }
        if ($this->frequency <= DataValue::FREQUENCY_YEAR) {
            $this->setYear((int) $this->date->format('Y'));
        }

        return $this;
    }

    /**
     * Get date
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Set hour
     */
    public function setHour(int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * Get hour
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * Set weekDay
     */
    public function setWeekDay(int $weekDay): self
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    /**
     * Get weekDay
     */
    public function getWeekDay(): int
    {
        return $this->weekDay;
    }

    /**
     * Set week
     *
     * @param int $week
     */
    public function setWeek(int $week): self
    {
        $this->week = $week;

        return $this;
    }

    /**
     * Get week
     */
    public function getWeek(): int
    {
        return $this->week;
    }

    /**
     * Set month
     *
     * @param int $month
     */
    public function setMonth(int $month): self
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * Set year
     */
    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Set frequency
     */
    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * Set feedData
     */
    public function setFeedData(FeedData $feedData): self
    {
        $this->feedData = $feedData;

        return $this;
    }

    /**
     * Get feedData
     */
    public function getFeedData(): FeedData
    {
        return $this->feedData;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'date' => $this->date,
            'frequency' => self::getFrequencyMachineName($this->frequency),
        ];
    }

    /**
     * Return the a date which is adapt to be the first day of a period
     *
     * If frequency is :
     *  * WEEK, return monday of the date's week
     *  * Month, return the first day of date's month
     *  * ...
     */
    public static function adaptToFrequency(\DateTimeImmutable $dateToAdapt, int $frequency): \DateTimeImmutable
    {
        $date = \DateTime::createFromImmutable($dateToAdapt);

        // Update date according to frequency.
        switch ($frequency) {
            case DataValue::FREQUENCY_HOUR:
                $date = new \DateTimeImmutable($date->format("Y-m-d H:00:00"));
                break;
            case DataValue::FREQUENCY_DAY:
                $date = new \DateTimeImmutable($date->format("Y-m-d 00:00:00"));
                break;
            case DataValue::FREQUENCY_WEEK:
                $w = 0 == $date->format('w') ? 6 : $date->format('w') - 1;
                $date->sub(new \DateInterval('P' . $w . 'D'));
                $date = new \DateTimeImmutable($date->format("Y-m-d 00:00:00"));
                break;
            case DataValue::FREQUENCY_MONTH:
                $date = new \DateTimeImmutable($date->format("Y-m-01 00:00:00"));
                break;
            case DataValue::FREQUENCY_YEAR:
                $date = new \DateTimeImmutable($date->format("Y-01-01 00:00:00"));
                break;
        }

        return $date;
    }

    public static function increaseToNextFrequence(\DateTimeImmutable $date, int $frequency): \DateTimeImmutable
    {
        $date = \DateTime::createFromImmutable($date);

        // Update date according to frequency.
        switch ($frequency) {
            case DataValue::FREQUENCY_HOUR:
                $date->add(new \DateInterval('PT1H'));
                break;
            case DataValue::FREQUENCY_DAY:
                $date->add(new \DateInterval('P1D'));
                break;
            case DataValue::FREQUENCY_WEEK:
                $date->add(new \DateInterval('P7D'));
                break;
            case DataValue::FREQUENCY_MONTH:
                $date->add(new \DateInterval('P1M'));
                break;
            case DataValue::FREQUENCY_YEAR:
                $date->add(new \DateInterval('P1Y'));
                break;
        }

        return \DateTimeImmutable::createFromMutable($date);
    }

    /**
     * Return first and last day of a pediod defined by a date and a frequency.
     *
     * return [
     *      'from' => period first day,
     *      'to' => period last day,
     *      'previousFrequency' => the frequence nelow the given frequency,
     * ]
     *
     *  * If frequence is MONTH and date is DD/MM/YYYY,
     *    [
     *      'from' => 01/MM/YYYY,
     *      'to' => 30/MM/YYYY or 31/MM/YYYY,
     *      'previousFrequency' => DAY,
     *    ]
     *  * If frequence is WEEK and date is DD/MM/YYYY,
     *    [
     *      'from' => first monday before DD/MM/YYYY,
     *      'to' => first sunday after DD/MM/YYYY,
     *      'previousFrequency' => DAY,
     *    ]
     *  * ...
     */
    public static function getAdaptedBoundariesForFrequency(\DateTimeImmutable $dateToAdapt, int $frequency): array
    {
        $date = \DateTime::createFromImmutable($dateToAdapt);

        switch ($frequency) {
            case DataValue::FREQUENCY_DAY:
                $firstDay = \DateTime::createFromImmutable($dateToAdapt);
                $lastDay = \DateTime::createFromImmutable($dateToAdapt);

                $lastDay->add(new \DateInterval('PT23H'));

                $previousFrequency = DataValue::FREQUENCY_HOUR;
                break;
            case DataValue::FREQUENCY_WEEK:
                $firstDay = \DateTime::createFromImmutable($dateToAdapt);
                $w = 0 == $date->format('w') ? 6 : $date->format('w') - 1;
                $firstDay->sub(new \DateInterval('P' . $w . 'D'));

                $lastDay = clone $firstDay;
                $lastDay->add(new \DateInterval('P6D'));

                $previousFrequency = DataValue::FREQUENCY_DAY;
                break;
            case DataValue::FREQUENCY_MONTH:
                $firstDay = \DateTime::createFromImmutable($dateToAdapt);
                $firstDay->sub(new \DateInterval('P' . ($date->format('d') - 1) . 'D'));

                $lastDay = clone $firstDay;
                $lastDay->add(new \DateInterval('P' . ($date->format('t') - 1) . 'D'));

                $previousFrequency = DataValue::FREQUENCY_DAY;
                break;
            case DataValue::FREQUENCY_YEAR:
                $firstDay = new \DateTime($date->format("Y-01-01 00:00:00"));

                $lastDay = new \DateTime($date->format("Y-12-31 00:00:00"));

                $previousFrequency = DataValue::FREQUENCY_MONTH;
                break;
            default:
                throw new InvalidArgumentException("Can't adapt boundaries for this frequency !");
        }

        return [
            'from' => \DateTimeImmutable::createFromMutable($firstDay),
            'to' => \DateTimeImmutable::createFromMutable($lastDay),
            'previousFrequency' => $previousFrequency,
        ];
    }
}
