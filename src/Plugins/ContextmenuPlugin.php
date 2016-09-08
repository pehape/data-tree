<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Plugins;

use Pehape\DataTree\Components;
use Pehape\Tools;

/**
 * ContextmenuPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class ContextmenuPlugin extends BasePlugin
{

    /** @var Tools\Objects\JObject */
    private $configuration;


    public function __construct(Components\DataTree $dataTree)
    {
        parent::__construct($dataTree);
        $this->scope = self::SCOPE_OUTER;
        $this->configuration = new Tools\Objects\JObject('defaultItems');
    }


    /** Render. */
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/contextmenu.latte');

        $template->treeId = $this->dataTree->getOption('elementId');
        $template->configuration = $this->configuration;
        $template->render();
    }

    


}
