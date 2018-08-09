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
 * CopyNodeEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class PasteNodeEvent extends BaseEvent
{

    /** @var array */
    protected $parameters = [
        'id' => 'data.node[0].id',
        'parent' => 'data.parent',
        'type' => 'data.node[0].type',
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
                $nodeId = $tree->getDataSource()->copyNode($parameters->id, $parameters->parent);
            } catch (Exceptions\DataSourceException $e) {
                $tree->sendErrorResponse([]);
            }

            $tree->sendSuccessResponse(['id' => $nodeId]);
        };
    }


}
