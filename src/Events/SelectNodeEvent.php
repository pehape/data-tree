<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

use Nette\Utils\ArrayHash;
use Pehape\DataTree\Components\DataTree;


/**
 * SelectNodeEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class SelectNodeEvent extends BaseEvent
{

    /** @var array */
    protected $parameters = [
        'id' => 'data.node.id',
        'type' => 'data.node.type',
    ];


    public function __construct()
    {
        parent::__construct();
        $this->refreshOnSuccess = FALSE;
        $this->callback = $this->getDefaultCallback();
    }

    public function getDefaultCallback()
    {
        return function (DataTree $tree, ArrayHash $parameters) {
            $node = $tree->getDataSource()->getNode($parameters->id);
            $tree->sendResponse($node->toArray());
        };
    }


}
