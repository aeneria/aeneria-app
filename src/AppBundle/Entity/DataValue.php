<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataValue
 *
 * @ORM\Table(name="data_value")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DataValueRepository")
 */
class DataValue
{
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FeedData")
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
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param float $value
     *
     * @return DataValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return DataValue
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set hour
     *
     * @param integer $hour
     *
     * @return DataValue
     */
    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * Get hour
     *
     * @return integer
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Set weekDay
     *
     * @param integer $weekDay
     *
     * @return DataValue
     */
    public function setWeekDay($weekDay)
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    /**
     * Get weekDay
     *
     * @return integer
     */
    public function getWeekDay()
    {
        return $this->weekDay;
    }

    /**
     * Set week
     *
     * @param integer $week
     *
     * @return DataValue
     */
    public function setWeek($week)
    {
        $this->week = $week;

        return $this;
    }

    /**
     * Get week
     *
     * @return integer
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * Set month
     *
     * @param integer $month
     *
     * @return DataValue
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return DataValue
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set frequency
     *
     * @param integer $frequency
     *
     * @return DataValue
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency
     *
     * @return integer
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set feedData
     *
     * @param \AppBundle\Entity\FeedData $feedData
     *
     * @return DataValue
     */
    public function setFeedData(\AppBundle\Entity\FeedData $feedData)
    {
        $this->feedData = $feedData;

        return $this;
    }

    /**
     * Get feedData
     *
     * @return \AppBundle\Entity\FeedData
     */
    public function getFeedData()
    {
        return $this->feedData;
    }
}
