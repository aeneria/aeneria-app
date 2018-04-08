<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedData
 *
 * @ORM\Table(name="feed_data")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedDataRepository")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $feed;

    /**
     * @var string
     *
     * @ORM\Column(name="data_type", type="string", length=255, unique=true)
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
     * @param \AppBundle\Entity\Feed $feed
     *
     * @return FeedData
     */
    public function setFeed(\AppBundle\Entity\Feed $feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \AppBundle\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }
}
