<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

use Nette\Utils;
use Pehape\DataTree\Components\DataTree;
use Pehape\DataTree\Exceptions;


/**
 * EventsTrait.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
trait EventsTrait
{

    /** @var callable[] function (DataTree $tree); Occurs when the data for tree are loaded */
    public $onLoadNodes = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a new node is created */
    public $onCreateNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a node is renamed */
    public $onRenameNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a node is moved */
    public $onMoveNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a node is deleted */
    public $onDeleteNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a node is selected */
    public $onSelectNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a node is pasted after copying */
    public $onCopyNode = [];

    /** @var callable[] function (DataTree $tree, array $parameters); Occurs when a tree is completely loaded */
    public $onLoaded = [];


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onLoadNodesCallback(DataTree $tree)
    {
        $nodes = $this->dataSource->getNodes();
        $mappedData = $this->dataMapper->applyMapping($nodes);
        $tree->sendResponse($mappedData);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onSelectNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        $node = $this->dataSource->getNode($parameters->id);
        $tree->sendResponse($node->toArray());
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onCreateNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        try {
            $nodeId = $this->dataSource->createNode($parameters->id, [
                'name' => $parameters->text,
                'type' => $parameters->type,
            ]);
        } catch (Exceptions\DataSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse(['id' => $nodeId]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onRenameNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        try {
            $this->dataSource->updateNode($parameters->id, ['name' => $parameters->text]);
        } catch (Exceptions\DataSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onMoveNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        try {
            $this->dataSource->moveNode($parameters->id, $parameters->parent);
        } catch (Exceptions\DataSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onCopyNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        try {
            $nodeId = $this->dataSource->copyNode($parameters->id, $parameters->parent);
        } catch (Exceptions\DataSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse(['id' => $nodeId]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onDeleteNodeCallback(DataTree $tree, Utils\ArrayHash $parameters)
    {
        try {
            $this->dataSource->removeNode($parameters->id);
        } catch (Exceptions\DataSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
    }


}
