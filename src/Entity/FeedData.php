<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;

/**
 * FeedData
 *
 * @ORM\Table(name="feed_data")
 * @ORM\Entity(repositoryClass="App\Repository\FeedDataRepository")
 */
class FeedData
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $feed;

    /**
     * @var string
     *
     * @ORM\Column(name="data_type", type="string", length=150, unique=true)
     */
    private $dataType;


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
     * Set dataType
     *
     * @param integer $dataType
     *
     * @return FeedData
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return integer
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set feed
     *
     * @param \App\Entity\Feed $feed
     *
     * @return FeedData
     */
    public function setFeed(\App\Entity\Feed $feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \App\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Update or Create a new DataValue and persist it.
     *
     * @param \DateInterval $date
     * @param int $frequency
     * @param string $value
     * @param EntityManager $entityManager
     */
    public function updateOrCreateValue(\DateTime $date, $frequency, $value , EntityManager &$entityManager)
    {
        // Update date according to frequnecy
        DataValue::adaptToFrequency($date, $frequency);

        $criteria = [
            'feedData' => $this,
            'date' => $date,
            'frequency' => $frequency,
        ];

        // Try to get the corresponding DataValue.
        $dataValue = $entityManager->getRepository('App:DataValue')->findOneBy($criteria);

        // Create it if it doesn't exist.
        if (!isset($dataValue)) {
            $dataValue = new DataValue();
            $dataValue->setFrequency($frequency);
            $dataValue->setFeedData($this);
            $dataValue->setDate($date);
        }

        if ($frequency <= DataValue::FREQUENCY['HOUR']) $dataValue->setHour($date->format('H'));
        $weekDay = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
        if ($frequency <= DataValue::FREQUENCY['DAY']) $dataValue->setWeekDay($weekDay);
        if ($frequency <= DataValue::FREQUENCY['WEEK']) $dataValue->setWeek($date->format('W'));
        if ($frequency <= DataValue::FREQUENCY['MONTH']) $dataValue->setMonth($date->format('m'));
        if ($frequency <= DataValue::FREQUENCY['YEAR']) $dataValue->setYear($date->format('Y'));

        $dataValue->setValue($value);

        // Persit the dataValue.
        $entityManager->persist($dataValue);
    }

    /**
     * Check if there's data in DB for $date for all $frequencies.
     * @param EntityManager $entityManager
     * @param \DateTime $date
     * @param $frequencies array of int from DataValue frequencies
     */
    public function isUpToDate(EntityManager $entityManager, \DateTime $date, array $frequencies)
    {
        $isUpToDate = TRUE;

        // Foreach frequency we check if we have a value for date.
        foreach ($frequencies as $frequency) {
            $criteria = [
                'feedData' => $this,
                'date' => DataValue::adaptToFrequency($date, $frequency),
                'frequency' => $frequency,
            ];

            // Try to get the corresponding DataValue.
            $dataValue = $entityManager->getRepository('App:DataValue')->findBy($criteria);

            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && !empty($dataValue);
        }
        return $isUpToDate;
    }

    /**
     * Get Date of last up to date data.
     * @param EntityManager $entityManager
     * @param $frequencies array of int from DataValue frequencies
     *
     * @return \Datetime
     */
    public function getLastUpToDate(EntityManager $entityManager)
    {
        // Try to get the corresponding DataValue.
        $result = $entityManager
            ->getRepository('App:DataValue')
            ->getLastValue($this, DataValue::FREQUENCY['DAY']);
        if (!empty($result)) {
            return new \DateTime($result[0]['date']);
        }

        return NULL;
    }
}
