<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Tests\Unit;

use Codeception\Util\Fixtures;
use Nette\DI\Container;
use Rathouz\DataTree\Components;


/**
 * Test Rathouz\DataTree\Components\DataTree class.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DataTreeTest extends \Codeception\Test\Unit
{

    /** @var \UnitTester */
    protected $tester;

    /** @var Container */
    private $container;

    /** @var Components\DataTree */
    private $dataTree;


    /** Before. */
    protected function _before()
    {
        $this->container = Fixtures::get('container');
        $dataTreeFactory = $this->container->getByType('Rathouz\DataTree\Components\IDataTree');
        $this->dataTree = $dataTreeFactory->create();
    }


    /** Test create instance via DI container. */
    public function testCreateInstance()
    {
        // Tested in _before().
    }


}
