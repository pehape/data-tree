<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Sources;

use Nette\Database\Context;
use Nette\Database\Table;
use Pehape\DataTree\Exceptions;


/**
 * DatabaseSource.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DatabaseSource implements IDataSource
{

    /** @var Context */
    private $db;

    /** @var string */
    private $baseTableName = self::DEF_BASE_TABLE_NAME;

    /** @var string */
    private $closureTableName = self::DEF_CLOSURE_TABLE_NAME;

    /** @var Selection */
    private $baseTable;

    /** @var Selection */
    private $closureTable;

    /** @var array */
    private $dbTables;

    /** Default table names. */
    const DEF_BASE_TABLE_NAME = 'data';
    const DEF_CLOSURE_TABLE_NAME = 'data_closure';


    /**
     * Constructor.
     * @param Context $db
     */
    public function __construct(Context $db)
    {
        $this->db = $db;
        $this->dbTables = $this->getStructure();
    }


    /**
     * Get nodes for the DataTree.
     * @param array $conditions
     * @return array
     */
    public function getNodes(array $conditions = [])
    {
        return $this->getTree($conditions);
    }


    /** @return string */
    public function getBaseTableName()
    {
        return $this->baseTableName;
    }


    /** @return string */
    public function getClosureTableName()
    {
        return $this->closureTableName;
    }


    /** @return Table\Selection */
    public function getBaseTable()
    {
        $this->baseTable = $this->db->table($this->baseTableName);
        return $this->baseTable;
    }


    /** @return Table\Selection */
    public function getClosureTable()
    {
        $this->closureTable = $this->db->table($this->closureTableName);
        return $this->closureTable;
    }


    /**
     * @param string $baseTableName
     * @return DatabaseSource
     * @throws Exceptions\UnknownSourceTableException
     */
    public function setBaseTableName($baseTableName)
    {
        if ($this->tableExists($baseTableName) === FALSE) {
            throw new Exceptions\UnknownSourceTableException($baseTableName);
        }

        $this->baseTableName = $baseTableName;
        return $this;
    }


    /**
     * @param string $closureTableName
     * @return DatabaseSource
     * @throws Exceptions\UnknownSourceTableException
     */
    public function setClosureTableName($closureTableName)
    {
        if ($this->tableExists($closureTableName) === FALSE) {
            throw new Exceptions\UnknownSourceTableException($closureTableName);
        }

        $this->closureTableName = $closureTableName;
        return $this;
    }


    /**
     * Select nodes for the DataTree.
     * @param array $conditions
     * @return array
     */
    private function getTree(array $conditions = [])
    {
        return $this->db->query("
            SELECT * 
                FROM $this->closureTableName closure
                LEFT JOIN $this->baseTableName data ON (data.id = closure.descendant)
                WHERE closure.depth = 1")->fetchAll();
    }


    /**
     * Get database tables.
     * @return array
     */
    private function getStructure()
    {
        $dbStructure = $this->db->getStructure()->getTables();
        $structure = [];
        foreach ($dbStructure as $dbItem) {
            $structure[] = $dbItem['name'];
        }

        return $structure;
    }


    /**
     * Check if table exists in database.
     * @param string $tableName
     * @return bool
     */
    private function tableExists($tableName)
    {
        return in_array($tableName, $this->dbTables);
    }


}
