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

    /** @var array */
    private $menu = [];

    /** @var array */
    private $submenu = [];

    /** @var array */
    private $disabledItemsOnId = [];

    /** @var array */
    private $disabledItemsOnType = [];


    /**
     * Comstructor.
     * @param Components\DataTree $dataTree
     */
    public function __construct(Components\DataTree $dataTree)
    {
        parent::__construct($dataTree);
        $this->configuration = new Tools\Objects\JObject('defaultItems');
    }


    /** Render configuration. */
    public function renderConfiguration()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/contextmenu.latte');

        $template->treeId = $this->dataTree->getOption('elementId');
        $template->configuration = $this->configuration;
        $template->menu = $this->menu;
        $template->submenu = $this->submenu;
        $template->disabledItemsOnId = $this->disabledItemsOnId;
        $template->disabledItemsOnType = $this->disabledItemsOnType;
        $template->render();
    }


    /**
     * Add new menu.
     * @param string $name
     * @param array $configuration
     * @return ContextmenuPlugin
     */
    public function addMenu($name, array $configuration)
    {
        $this->menu[$name] = $configuration;
        return $this;
    }


    /**
     * Add new submenu to existing menu.
     * @param type $parentName
     * @param array $items
     * @return ContextmenuPlugin
     */
    public function addSubmenu($parentName, array $items)
    {
        $this->submenu[$parentName] = $items;
        return $this;
    }


    /**
     * Disable given menu items on specific node id.
     * @param int $id
     * @param array $items
     * @return ContextmenuPlugin
     */
    public function disableItemsOnId($id, array $items)
    {
        $this->disabledItemsOnId[$id] = $items;
        return $this;
    }


    /**
     * Disable given menu items on specific node type.
     * @param string $type
     * @param array $items
     * @return ContextmenuPlugin
     */
    public function disableItemsOnType($type, array $items)
    {
        $this->disabledItemsOnType[$type] = $items;
        return $this;
    }


}
