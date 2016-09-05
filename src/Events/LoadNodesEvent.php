<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

use Pehape\DataTree\Components\DataTree;

/**
 * LoadNodesEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class LoadNodesEvent extends BaseEvent
{

    /** Constructor. */
    public function __construct()
    {
        parent::__construct();
        $this->type = self::TYPE_LOAD;
        $this->callback = [$this, 'getDefaultCallback'];
    }


    public function render()
    {
    }


    public function getDefaultCallback()
    {
        return function (DataTree $tree) {
            $nodes = $tree->getDataSource()->getNodes();
            $mappedData = $tree->getDataMapper()->applyMapping($nodes);
            $tree->sendResponse($mappedData);
        };
    }

}
