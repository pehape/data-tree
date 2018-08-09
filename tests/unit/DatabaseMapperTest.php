<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Tests\Unit;

use Codeception\Util\Fixtures;
use Nette\Database\Context;
use Nette\DI\Container;
use Rathouz\DataTree\Mappers\DatabaseMapper;
use Rathouz\DataTree\Sources\DatabaseSource;


/**
 * Test Rathouz\DataTree\Mappers\DatabaseMapper class.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DatabaseMapperTest extends \Codeception\Test\Unit
{

    /** @var \UnitTester */
    protected $tester;

    /** @var Container */
    private $container;

    /** @var Context */
    private $db;

    /** @var DatabaseSource */
    private $source;

    /** @var DatabaseMapper */
    private $mapper;


    /** Before. */
    protected function _before()
    {
        $this->container = Fixtures::get('container');
        $this->db = $this->container->getByType('Nette\Database\Context');
        $this->source = new DatabaseSource($this->db);
        $this->mapper = new DatabaseMapper();
    }


    /** DatabaseSource must implement \Rathouz\DataTree\Mappers\IDataMapper. */
    public function testImplementsInterface()
    {
        $this->tester->assertInstanceOf('Rathouz\DataTree\Mappers\IDataMapper', $this->mapper);
        $this->tester->assertInstanceOf('Rathouz\DataTree\Mappers\IMapper', $this->mapper);
    }


    /** Mapping data must be of type array. */
    public function testMappingIsArray()
    {
        $this->tester->assertTrue(is_array($this->mapper->getMapping()));
    }


    /** Test applying of minimal mapping. */
    public function testApplyMinimalMapping()
    {
        $this->mapper->setMapping(['id' => 'id', 'ancestor' => 'parent', 'name' => 'text']);
        $mappedData = $this->mapper->applyMapping($this->source->getNodes());
        $this->tester->assertTrue(is_array($mappedData));
        $this->assertEquals(3, count(array_keys($mappedData[0])));
    }


    /** Test applying of wrong mapping. */
    public function testApplyWrongMapping()
    {
        $this->mapper->setMapping(['id' => 'id', 'ancestor' => 'parent', 'text' => 'text']);
        $nodes = $this->source->getNodes();
        $this->tester->expectException('Rathouz\DataTree\Exceptions\UnvalidDataMappingException', function() use ($nodes) {
            $this->mapper->applyMapping($nodes);
        });
    }


    /** Test applying of minimal mapping. */
    public function testApplyMappingToEmptyData()
    {
        $mappedData = $this->mapper->applyMapping([]);
        $this->tester->assertEmpty($mappedData);
    }


}
