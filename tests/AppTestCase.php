<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AppTestCase extends KernelTestCase
{
    use AppTestTrait;

    private $_container;
    private $_kernel;

    /**
     * Get kernel
     */
    final protected function getKernel(): KernelInterface
    {
        return $this->_kernel ?? (
            $this->_kernel = self::bootKernel()
        );
    }

    /**
     * Get container
     */
    final protected function getContainer(): ContainerInterface
    {
        return $this->_container ?? (
            $this->_container = $this->getKernel()->getContainer()
        );
    }
}