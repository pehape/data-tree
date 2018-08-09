<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Rendering;

use Latte;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Utils;
use Rathouz\DataTree\Components\DataTree;
use Rathouz\DataTree\Events;


/**
 * AdminlteRenderer.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class AdminlteRenderer implements IRenderer
{

    /** @var Template */
    private $template;


    /** Constructor. */
    public function __construct()
    {
        $this->template = new Template(new Latte\Engine());
    }


    /**
     * Rendering.
     * @param DataTree $dataTree
     */
    public function render(DataTree $dataTree)
    {
        $this->template->setFile(__DIR__ . '/templates/adminlte.latte');
        $this->template->dataTree = $dataTree;
        $this->template->setTranslator($dataTree->getTranslator());
        $this->template->controlName = $dataTree->getControlPath();
        $this->template->plugins = $dataTree->getPlugins();
        $this->template->events = $dataTree->getEvents(Events\BaseEvent::TYPE_NODE);
        $loadEvents = $dataTree->getEvents(Events\BaseEvent::TYPE_LOAD);
        $loadEvent = array_pop($loadEvents);
        $this->template->loadDataCallback = substr($loadEvent->name, strlen(Events\BaseEvent::PREFIX));
        $this->template->options = Utils\ArrayHash::from($dataTree->getOptions());
        $this->template->isAjax = $dataTree->presenter->isAjax();
        $this->template->render();
    }


}
