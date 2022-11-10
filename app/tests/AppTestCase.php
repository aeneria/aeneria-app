<?php

declare(strict_types=1);

namespace App\Tests;

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
}
