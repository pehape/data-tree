<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Plugins;

use Rathouz\DataTree\Components;


/**
 * SearchPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class SearchPlugin extends BasePlugin
{

    /** @var string */
    private $inputId;

    /** @var string */
    private $inputClass;


    /**
     * Construct.
     * @param Components\DataTree $dataTree
     */
    public function __construct(Components\DataTree $dataTree)
    {
        parent::__construct($dataTree);
        $this->inputId = $dataTree->getOption('elementId') . '_search_' . time();
    }

    /** Render. */
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/search-plugin.latte');
        $template->shortname = $this->getShortname();
        $template->options = $this->options->toJson();
        $template->render();
    }

    /** Render configuration. */
    public function renderConfiguration()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/search.latte');

        $template->treeId = $this->dataTree->getOption('elementId');
        $template->inputId = $this->inputId;
        $template->inputClass = $this->inputClass;
        $template->configuration = $this->configuration;
        $template->render();
    }


    /** @return string */
    public function getInputId()
    {
        return $this->inputId;
    }


    /**
     * @param string $inputId
     * @return SearchPlugin
     */
    public function setInputId($inputId)
    {
        $this->inputId = $inputId;
        return $this;
    }


    /** @return string */
    public function getInputClass()
    {
        return $this->inputClass;
    }


    /**
     * @param string $inputClass
     * @return SearchPlugin
     */
    public function setInputClass($inputClass)
    {
        $this->inputClass = $inputClass;
        return $this;
    }


}
