<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedRelation
 *
 * @ORM\Table(name="feed_relation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedRelationRepository")
 */
class FeedRelation
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $primaryFeed;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $secondaryFeed;


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
     * Set primaryFeed
     *
     * @param \AppBundle\Entity\Feed $primaryFeed
     *
     * @return FeedRelation
     */
    public function setPrimaryFeed(\AppBundle\Entity\Feed $primaryFeed)
    {
        $this->primaryFeed = $primaryFeed;

        return $this;
    }

    /**
     * Get primaryFeed
     *
     * @return \AppBundle\Entity\Feed
     */
    public function getPrimaryFeed()
    {
        return $this->primaryFeed;
    }

    /**
     * Set secondaryFeed
     *
     * @param \AppBundle\Entity\Feed $secondaryFeed
     *
     * @return FeedRelation
     */
    public function setSecondaryFeed(\AppBundle\Entity\Feed $secondaryFeed)
    {
        $this->secondaryFeed = $secondaryFeed;

        return $this;
    }

    /**
     * Get secondaryFeed
     *
     * @return \AppBundle\Entity\Feed
     */
    public function getSecondaryFeed()
    {
        return $this->secondaryFeed;
    }
}
