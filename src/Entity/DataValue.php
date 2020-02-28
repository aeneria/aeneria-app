<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataValue
 *
 * @ORM\Table(name="data_value")
 * @ORM\Entity(repositoryClass="App\Repository\DataValueRepository")
 */
class DataValue
{
    const FREQUENCY = [
        'HOUR' => 1,
        'DAY' => 2,
        'WEEK' => 3,
        'MONTH' => 4,
        'YEAR' => 5,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="hour", type="integer", nullable=true)
     */
    private $hour;

    /**
     * @var int
     *
     * @ORM\Column(name="week_day", type="integer", nullable=true)
     */
    private $weekDay;

    /**
     * @var int
     *
     * @ORM\Column(name="week", type="integer", nullable=true)
     */
    private $week;

    /**
     * @var int
     *
     * @ORM\Column(name="month", type="integer", nullable=true, nullable=true)
     */
    private $month;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    private $year;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FeedData")
     * @ORM\JoinColumn(nullable=false)
     */
    private $feedData;

    /**
     * @var int
     *
     * @ORM\Column(name="frequency", type="integer")
     */
    private $frequency;

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     */
    public function getDate(): \DateTime
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
     * @param integer $week
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
     * @param integer $month
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
    public function setFeedData(\App\Entity\FeedData $feedData): self
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

    /**
     * Return the a date which is adapt to be the first day of a period
     *
     * If frequency is :
     *  * WEEK, return monday of the date's week
     *  * Month, return the first day of date's month
     *  * ...
     */
    public static function adaptToFrequency(\DateTime $date, int $frequency): \DateTime
    {
        // Update date according to frequency.
        switch ($frequency) {
            case DataValue::FREQUENCY['HOUR'] :
                $date = new \DateTime($date->format("Y-m-d H:00:00"));
                break;
            case DataValue::FREQUENCY['DAY'] :
                $date = new \DateTime($date->format("Y-m-d 00:00:00"));
                break;
            case DataValue::FREQUENCY['WEEK'] :
                $w = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
                $date->sub(new \DateInterval('P' . $w . 'D'));
                $date = new \DateTime($date->format("Y-m-d 00:00:00"));
                break;
            case DataValue::FREQUENCY['MONTH'] :
                $date = new \DateTime($date->format("Y-m-01 00:00:00"));
                break;
            case DataValue::FREQUENCY['YEAR'] :
                $date = new \DateTime($date->format("Y-01-01 00:00:00"));
                break;
        }

        return $date;
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
    public static function getAdaptedBoundariesForFrequency(\DateTime $date, int $frequency): array
    {
        switch ($frequency) {
            case DataValue::FREQUENCY['DAY']:
                $firstDay = clone $date;
                $lastDay = clone $date;

                $lastDay->add(new \DateInterval('P1D'));

                $previousFrequency = DataValue::FREQUENCY['HOUR'];
                break;
            case DataValue::FREQUENCY['WEEK']:
                $firstDay = clone $date;
                $w = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
                $firstDay->sub(new \DateInterval('P' . $w . 'D'));

                $lastDay = clone $firstDay;
                $lastDay->add(new \DateInterval('P6D'));

                $previousFrequency = DataValue::FREQUENCY['DAY'];
                break;
            case DataValue::FREQUENCY['MONTH']:
                $firstDay = clone $date;
                $firstDay->sub(new \DateInterval('P' . ($date->format('d') - 1) . 'D'));

                $lastDay = clone $firstDay;
                $lastDay->add(new \DateInterval('P' . ($date->format('t')) . 'D'));

                $previousFrequency = DataValue::FREQUENCY['DAY'];
                break;
            case DataValue::FREQUENCY['YEAR']:
                $firstDay = new \DateTime($date->format("Y-1-1 00:00:00"));;

                $lastDay = clone $firstDay;
                $lastDay->add(new \DateInterval('P1Y'));

                $previousFrequency = DataValue::FREQUENCY['MONTH'];
                break;
        }

        return [
            'from' => $firstDay,
            'to' => $lastDay,
            'previousFrequency' => $previousFrequency,
        ];
    }
}
