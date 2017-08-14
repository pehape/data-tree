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

    /** @var string */
    private $order = self::ORDER_ASC;

    /** Default table names. */
    const DEF_BASE_TABLE_NAME = 'data';
    const DEF_CLOSURE_TABLE_NAME = 'data_closure';

    /** Orders */
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    private $defaultOrder = NULL;
    private $defaultConditions = NULL;

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
     * @param string $order
     * @return array
     */
    public function getNodes(array $conditions = [])
    {
        return $this->getTree($conditions, $this->order);
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
     * @return array
     */
    public function getChildrenOf($id)
    {
        return $this->db->query('SELECT c.*, cc2.ancestor, cc.descendant, cc.depth
            FROM ' . $this->getBaseTableName() . ' c
            JOIN ' . $this->getClosureTableName() . ' cc ON (c.id = cc.descendant)
            LEFT JOIN ' . $this->getClosureTableName() . ' cc2 ON (cc2.descendant = cc.descendant AND cc2.depth = 1)
            WHERE cc.ancestor = ? AND cc.depth = ?', $id, 1)->fetchAll();
    }


    /**
     * Get children from node.
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
     * Get children count of node.
     * @param int $id
     * @param string|NULL $type
     * @return int
     */
    public function getChildrenCountFrom($id, $type = NULL)
    {
        $selection = $this->getClosureTable()->where('ancestor', $id);
        return ($type === NULL) ?
            ($selection->count() - 1) :
            ($selection->where('descendant.type', $type)->count() - 1);
    }


    /**
     * Get parent of node.
     * @param int $id
     * @return Table\IRow|NULL
     */
    public function getParentOf($id)
    {
        return $this->getBaseTable()
                ->select($this->baseTableName . '.*, depth')
                ->where(':' . $this->closureTableName . '.descendant', $id)
                ->where(':' . $this->closureTableName . '.depth', 1)
                ->fetch();
    }


    /**
     * Get parents of node.
     * @param int $id
     * @param bool $self
     * @param int $limit
     * @return array
     */
    public function getParentsFrom($id, $self = TRUE, $limit = NULL, $order = 'ASC')
    {
        $selection = $this->getBaseTable()
            ->select($this->baseTableName . '.*, depth')
            ->where(':' . $this->closureTableName . '.descendant', $id)
            ->order('depth ' . $order);
        
        if ($self === FALSE) {
            $selection->where('depth > ?', 0);
        }

        if (is_int($limit) === TRUE) {
            $selection->limit($limit);
        }

        return $selection->fetchAll();
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
     * Set data ordering.
     * @param string $order
     * @return DatabaseSource
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }


    /**
     * Select nodes for the DataTree.
     * @param array $conditions
     * @param string $order
     * @return array
     */
    private function getTree(array $conditions = [], $order = self::ORDER_ASC)
    {
        $query = "
            SELECT * 
                FROM $this->closureTableName closure
                LEFT JOIN $this->baseTableName data ON (data.id = closure.descendant)
                WHERE closure.depth = 1 ";
        // Add default conditions
        if ($this->defaultConditions !== NULL) {
            $query .= "AND $this->defaultConditions ";
        }
        
        foreach ($conditions as $name => $content) {
            switch ($name) {
                case 'ancestor':
                    $query .= "AND closure.ancestor = $content AND closure.depth = 1 ";
                    break;
            }
        }
        
        $query .= "ORDER BY ";
        if ($this->defaultOrder !== NULL) {
            $query .= "$this->defaultOrder, ";
        }
        
        return $this->db->query($query . "data.name $order")->fetchAll();
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

    public function setDefaultOrder($defaultOrder)
    {
        $this->defaultOrder = $defaultOrder;
        return $this;
    }


    public function setDefaultConditions($defaultConditions)
    {
        $this->defaultConditions = $defaultConditions;
        return $this;
    }



}
