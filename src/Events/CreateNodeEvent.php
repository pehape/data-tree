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
 * CreateNodeEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class CreateNodeEvent extends BaseEvent
{

    /** @var array */
    protected $parameters = [
        'id' => 'data.node.parent',
        'text' => 'data.node.text',
        'type' => 'data.node.type',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->callback = [$this, 'getDefaultCallback'];
    }

    public function render()
    {
        $template = $this->template->setFile(__DIR__ . '/templates/create.latte');
        $template->eventRealname = $this->getRealname() . self::SUFFIX;
        $template->eventCallback = $this->getParent()->link('callback', [$this->getRealname()]);
        $template->parametersString = $this->getParametersString();
        $template->controlName = $this->getParent()->getControlPath();
        $template->refreshOnSuccess = $this->refreshOnSuccess;
        $template->refreshOnError = $this->refreshOnError;
        $template->joinTree = $this->getParent()->getOption('joinTree') === NULL ? 'null' : $this->getParent()->getOption('joinTree');
        $template->render();
    }


    public function getDefaultCallback()
    {
        return function (DataTree $tree, ArrayHash $parameters) {
            try {
                $nodeId = $tree->getDataSource()->createNode($parameters->id, [
                    'name' => $parameters->text,
                    'type' => $parameters->type,
                ]);
            } catch (Exceptions\DataSourceException $e) {
                $tree->sendErrorResponse([]);
            }

            $tree->sendSuccessResponse(['id' => $nodeId]);
        };
    }


}
