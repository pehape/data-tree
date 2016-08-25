<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

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

}
