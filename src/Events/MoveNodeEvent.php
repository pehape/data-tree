<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

use Nette\Utils\ArrayHash;
use Pehape\DataTree\Components\DataTree;
use Pehape\DataTree\Exceptions;

/**
 * MoveNodeEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class MoveNodeEvent extends BaseEvent
{

    /** @var array */
    protected $parameters = [
        'id' => 'data.node.id',
        'parent' => 'data.parent',
        'type' => 'data.node.type',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->callback = $this->getDefaultCallback();
    }

    public function getDefaultCallback()
    {
        return function (DataTree $tree, ArrayHash $parameters) {
            try {
                $tree->getDataSource()->moveNode($parameters->id, $parameters->parent);
            } catch (Exceptions\DataSourceException $e) {
                $tree->sendErrorResponse([]);
            }

            $tree->sendSuccessResponse([]);
        };
    }


}
