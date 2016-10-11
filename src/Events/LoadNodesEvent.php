<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

use Nette\Utils\ArrayHash;
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
        $this->callback = $this->getDefaultCallback();
    }


    public function render()
    {
    }


    public function getDefaultCallback()
    {
        return function (DataTree $tree, ArrayHash $parameters) {

            $nodes = (isset($parameters->nodeId) === TRUE) ?
                $tree->getDataSource()->getNodes(['ancestor' => $parameters->nodeId]) :
                $tree->getDataSource()->getNodes();
            $mappedData = $tree->getDataMapper()->applyMapping($nodes);

            array_walk($mappedData, function (& $item) use ($tree, $parameters) {

                if (isset($parameters->nodeId) === TRUE) {
                    // Lazy loading
                    $childrenCount = $tree->getDataSource()->getChildrenCountFrom($item['id']);
                    $item['children'] = ($childrenCount > 0);
                }

                if (in_array($item['id'], $tree->getSelectedNodes()) === TRUE) {
                    $item['state']['selected'] = TRUE;
                }

                if (in_array($item['id'], $tree->getOpenedNodes()) === TRUE) {
                    $item['state']['opened'] = TRUE;
                }

                if (in_array($item['id'], $tree->getDisabledNodes()) === TRUE) {
                    $item['state']['disabled'] = TRUE;
                }
            });

            $tree->sendResponse($mappedData);
        };
    }


}
