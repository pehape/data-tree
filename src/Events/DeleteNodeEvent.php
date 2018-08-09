<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Events;

use Nette\Utils\ArrayHash;
use Rathouz\DataTree\Components\DataTree;
use Rathouz\DataTree\Exceptions;

/**
 * DeleteNodeEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DeleteNodeEvent extends BaseEvent
{

    /** @var array */
    protected $parameters = [
        'id' => 'data.node.id',
        'nodes' => 'data.node.children_d',
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
                $tree->getDataSource()->removeNode($parameters->id);
            } catch (Exceptions\DataSourceException $e) {
                $tree->sendErrorResponse([]);
            }

            $tree->sendSuccessResponse([]);
        };
    }


}
