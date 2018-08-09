<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Plugins;

use Nette\Application\UI;
use Rathouz\DataTree\Components;
use Rathouz\Tools;


/**
 * BasePlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
abstract class BasePlugin extends UI\Control implements IPlugin
{

    /** Scopes */
    const PREFIX = 'plugin_';

    /** @var Components\DataTree */
    protected $dataTree;

    /** @var Tools\Objects\JObject */
    protected $options;

    /** @var Tools\Objects\JObject */
    protected $configuration;


    /**
     * Constructor.
     * @param Components\DataTree $dataTree
     */
    public function __construct(Components\DataTree $dataTree)
    {
        parent::__construct();
        $this->dataTree = $dataTree;
        $this->options = new Tools\Objects\JObject('');
        $this->configuration = new Tools\Objects\JObject('tree');
    }


    /** Render. */
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/plugin.latte');
        $template->shortname = $this->getShortname();
        $template->options = $this->options->toJson();
        $template->render();
    }


    /** Render configuration. */
    public function renderConfiguration()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/configuration.latte');
        $template->configuration = $this->configuration;
        $template->render();
    }


    /** @return Tools\Objects\JObject */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /** @return string */
    public function getShortname()
    {
        return substr($this->name, strlen(self::PREFIX));
    }


}
