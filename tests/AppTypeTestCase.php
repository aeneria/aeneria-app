<?php

declare(strict_types=1);

namespace App\Tests;

use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Test\TypeTestCase;

abstract class AppTypeTestCase extends TypeTestCase
{
    use AppTestTrait;

    private $_container;

    /**
     * Get container
     */
    final protected function getContainer(): ContainerInterface
    {
        return $this->_container ?? (
            $this->_container = $this->factory->getContainer()
        );
    }
}
