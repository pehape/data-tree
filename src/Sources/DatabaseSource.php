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

    /** @var bool */
    private $selfTransaction = FALSE;

    /** Default table names. */
    const DEF_BASE_TABLE_NAME = 'data';
    const DEF_CLOSURE_TABLE_NAME = 'data_closure';


    /**
     * Constructor.
     * @param Context $db
     * @throws Exceptions\UnknownSourceTableException
     */
    public function __construct(Context $db, $baseTableName = NULL, $closureTableName = NULL)
    {
        $this->db = $db;
        $this->dbTables = $this->getStructure();

        if ($baseTableName !== NULL) {
            $this->setBaseTableName($baseTableName);
        }

        if ($closureTableName !== NULL) {
            $this->setClosureTableName($closureTableName);
        }
    }


    /**
     * Get node.
     * @param int $id
     * @return Table\IRow
     * @throws Exceptions\DataSourceException
     */
    public function getNode($id)
    {
        $node = $this->getBaseTable()->get($id);
        if ($node === FALSE) {
            throw new Exceptions\DataSourceException('Node could not be found.');
        }

        return $node;
    }


    /**
     * Get nodes.
     * @param array $conditions
     * @return array
     */
    public function getNodes(array $conditions = [])
    {
        return $this->getTree($conditions);
    }


    /**
     * Create new node.
     * @param int $parentId
     * @param array $data
     * @return int
     * @throws Exceptions\DataSourceException
     */
    public function createNode($parentId, array $data)
    {
        $this->beginTransaction();
        try {
            $operation = $this->getBaseTable()->insert($data);
            if ($operation === FALSE) {
                throw new Exceptions\DataSourceException('Node could not be created.');
            }

            $baseRelInsert = $this->getClosureTable()->insert([
                'ancestor' => $operation->id,
                'descendant' => $operation->id,
                'depth' => 0,
            ]);
            $parentRels = $this->getClosureTable()
                ->select('ancestor, ' . $operation->id . ' AS descendant, depth+1 AS depth')
                ->where('descendant', $parentId)
                ->fetchAll();
            $parentRelsInsert = $this->getClosureTable()->insert($parentRels);
            if ($baseRelInsert === FALSE || $parentRelsInsert === FALSE) {
                throw new Exceptions\DataSourceException('Tree could not be updated');
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw new Exceptions\DataSourceException($e->getMessage());
        }

        return $operation->id;
    }


    /**
     * Update node.
     * @param int $id
     * @param array $data
     * @throws Exceptions\DataSourceException
     */
    public function updateNode($id, array $data)
    {
        try {
            $updateResult = $this->getBaseTable()->get($id)->update($data);
            if ($updateResult === FALSE) {
                throw new Exceptions\DataSourceException('Node could not be updated.');
            }
        } catch (\Exception $e) {
            throw new Exceptions\DataSourceException($e->getMessage());
        }
    }


    /**
     * Move node.
     * @param int $id
     * @param int $parentId
     * @throws Exceptions\DataSourceException
     */
    public function moveNode($id, $parentId)
    {
        $this->beginTransaction();
        try {
            $this->db->query('DELETE cc_a FROM ' . $this->getClosureTableName() . ' cc_a
                JOIN ' . $this->getClosureTableName() . ' cc_d USING(descendant)
                LEFT JOIN ' . $this->getClosureTableName() . ' cc_x
                ON cc_x.ancestor = cc_d.ancestor AND cc_x.descendant = cc_a.ancestor
                WHERE cc_d.ancestor = ? AND cc_x.ancestor IS NULL', $id);
            $this->db->query('INSERT INTO ' . $this->getClosureTableName() . ' (ancestor, descendant, depth)
                SELECT supertree.ancestor, subtree.descendant, supertree.depth+subtree.depth+1
                FROM ' . $this->getClosureTableName() . ' AS supertree JOIN ' . $this->getClosureTableName() . ' AS subtree
                WHERE subtree.ancestor = ?
                AND supertree.descendant = ?', $id, $parentId);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw new Exceptions\DataSourceException($e->getMessage());
        }
    }


    /**
     * Copy node and replace data.
     * @param int $nodeId
     * @param int $parentId
     * @param array $replacement
     * @return int
     * @throws Exceptions\DataSourceException
     * @todo Recursive copy
     */
    public function copyNode($nodeId, $parentId, array $replacement = [], $recursive = TRUE)
    {
        $this->beginTransaction();
        try {
            $node = $this->getNode($nodeId);
            $data = [];
            foreach ($node as $key => $value) {
                if ($key === 'id') {
                    continue;
                }

                if (array_key_exists($key, $replacement) === TRUE) {
                    $data[$key] = $replacement[$key];
                } else {
                    $data[$key] = $value;
                }
            }

            $newId = $this->createNode($parentId, $data);
            if ($recursive === TRUE) {
                $nodeChildren = $this->getChildrenFrom($nodeId, FALSE);
                foreach ($nodeChildren as $nodeChild) {
                    if ($nodeChild->depth !== 1) {
                        continue;
                    }

                    $this->copyNode($nodeChild->id, $newId, $replacement, TRUE);
                }
            }

            $this->commit();
            return $newId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw new Exceptions\DataSourceException($e->getMessage());
        }
    }


    /**
     * Remove node.
     * @param int $id
     * @throws Exceptions\DataSourceException
     */
    public function removeNode($id)
    {
        $children = $this->getChildrenFrom($id);
        array_walk($children, function (& $item) {
            $item = $item->id;
        });
        try {
            $this->getBaseTable()->where('id', $children)->delete();
        } catch (\Exception $e) {
            throw new Exceptions\DataSourceException($e->getMessage());
        }
    }


    /**
     * Get children of node.
     * @param int $id
     * @param bool $self
     * @return array
     */
    public function getChildrenFrom($id, $self = TRUE)
    {
        if ($self === TRUE) {
            $data = $this->db->query('SELECT c.*, cc2.ancestor, cc.descendant, cc.depth
                FROM ' . $this->getBaseTableName() . ' c
                JOIN ' . $this->getClosureTableName() . ' cc ON (c.id = cc.descendant)
                LEFT JOIN ' . $this->getClosureTableName() . ' cc2 ON (cc2.descendant = cc.descendant AND cc2.depth = 1)
                WHERE cc.ancestor = ?', $id)->fetchAll();
        } else {
            $data = $this->db->query('SELECT c.*, cc2.ancestor, cc.descendant, cc.depth
                FROM ' . $this->getBaseTableName() . ' c
                JOIN ' . $this->getClosureTableName() . ' cc ON (c.id = cc.descendant)
                LEFT JOIN ' . $this->getClosureTableName() . ' cc2 ON (cc2.descendant = cc.descendant AND cc2.depth = 1)
                WHERE cc.ancestor = ? AND cc.depth > 0', $id)->fetchAll();
        }

        return $data;
    }


    /**
     * Get parents of node.
     * @param int $id
     * @param bool $self
     * @return array
     */
    public function getParentsFrom($id, $self = TRUE)
    {
        if ($self === TRUE) {
            return $this->db->query('SELECT c.*, depth FROM ' . $this->getBaseTableName() . ' c
                JOIN ' . $this->getClosureTableName() . ' cc ON (c.id = cc.ancestor)
                WHERE cc.descendant = ? ORDER BY depth ASC', $id)->fetchAll();
        } else {
            return $this->db->query('SELECT c.*, depth FROM ' . $this->getBaseTableName() . ' c
                JOIN ' . $this->getClosureTableName() . ' cc ON (c.id = cc.ancestor)
                WHERE cc.descendant = ?  AND depth > ? ORDER BY depth ASC', $id, 0)->fetchAll();
        }
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


    /** @return bool */
    private function inTransaction()
    {
        return $this->db->getConnection()->getPdo()->inTransaction();
    }


    /** Begin transaction if there is no active active transaction. */
    private function beginTransaction()
    {
        if ($this->inTransaction() === FALSE) {
            $this->db->beginTransaction();
            $this->selfTransaction = TRUE;
        }
    }


    /** Commit self transaction. */
    private function commit()
    {
        if ($this->selfTransaction === TRUE) {
            $this->db->commit();
            $this->selfTransaction = FALSE;
        }
    }


    /** Rollback self transaction. */
    private function rollBack()
    {
        if ($this->selfTransaction === TRUE) {
            $this->db->rollBack();
            $this->selfTransaction = FALSE;
        }
    }


}
