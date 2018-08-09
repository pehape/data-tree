<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Sources;

/**
 * IDataSource.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IDataSource
{


    /**
     * Get node with given id.
     * @param int $id
     * @return mixed
     */
    public function getNode($id);


    /**
     * Get nodes for the DataTree.
     * @param array $conditions
     * @param string $order
     * @return mixed
     */
    public function getNodes(array $conditions);


    /**
     * Create new node.
     * @param int $parentId
     * @param array $data
     * @return int
     */
    public function createNode($parentId, array $data);


    /**
     * Update node.
     * @param type $id
     * @param array $data
     * @return mixed
     */
    public function updateNode($id, array $data);


    /**
     * Move node.
     * @param int $id
     * @param int $parentId
     * @return mixed
     */
    public function moveNode($id, $parentId);


    /**
     * Move node.
     * @param int $id
     * @param int $parentId
     * @return mixed
     */
    public function copyNode($id, $parentId, array $replacement, $recursive);


    /**
     * Remove node.
     * @param int $id
     * @return mixed
     */
    public function removeNode($id);
}
