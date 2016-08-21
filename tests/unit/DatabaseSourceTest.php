<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Tests\Unit;

use Codeception\Util\Fixtures;
use Nette\Database\Context;
use Nette\DI\Container;
use Pehape\DataTree\Sources\DatabaseSource;


/**
 * Test Pehape\DataTree\Sources\DatabaseSource class.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DatabaseSourceTest extends \Codeception\Test\Unit
{

    /** @var \UnitTester */
    protected $tester;

    /** @var Container */
    private $container;

    /** @var Context */
    private $db;

    /** @var DatabaseSource */
    private $source;


    /** Before. */
    protected function _before()
    {
        $this->container = Fixtures::get('container');
        $this->db = $this->container->getByType('Nette\Database\Context');
        $this->source = new DatabaseSource($this->db);
    }


    /** DatabaseSource must implement \Pehape\DataTree\Sources\IDataSource. */
    public function testImplementsInterface()
    {
        $this->tester->assertInstanceOf('Pehape\DataTree\Sources\IDataSource', $this->source);
    }


    /** Test default table names. */
    public function testDefaultTablesNames()
    {
        $this->tester->assertEquals(DatabaseSource::DEF_BASE_TABLE_NAME, $this->source->getBaseTableName());
        $this->tester->assertEquals(DatabaseSource::DEF_CLOSURE_TABLE_NAME, $this->source->getClosureTableName());
    }


    /** Test custom table names. */
    public function testCustomTableNames()
    {
        $baseTableName = 'data';
        $closureTableName = 'data_closure';
        $this->source->setBaseTableName($baseTableName);
        $this->source->setClosureTableName($closureTableName);
        $this->tester->assertEquals($baseTableName, $this->source->getBaseTableName());
        $this->tester->assertEquals($closureTableName, $this->source->getClosureTableName());
    }


    /** Test get tables selection. */
    public function testGetTablesSelection()
    {
        $this->tester->assertInstanceOf('Nette\Database\Table\Selection', $this->source->getBaseTable());
        $this->tester->assertInstanceOf('Nette\Database\Table\Selection', $this->source->getClosureTable());
    }


    /** Test that exception is thrown when setting unknow table. */
    public function testSetUnknownTable()
    {
        $this->tester->expectException('Pehape\DataTree\Exceptions\UnknownSourceTableException', function() {
            $this->source->setBaseTableName('unknown');
        });
        $this->tester->expectException('Pehape\DataTree\Exceptions\UnknownSourceTableException', function() {
            $this->source->setBaseTableName('unknown_closure');
        });
    }


    /** Test that method getNodes() always returns an array. */
    public function testGetNodes()
    {
        $this->tester->assertTrue(is_array($this->source->getNodes()));
        $this->db->table('data')->delete();
        $this->tester->assertTrue(is_array($this->source->getNodes()));
        $this->tester->assertEmpty($this->source->getNodes());
    }


}
