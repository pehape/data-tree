<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Events;

use Nette\Application\UI;


/**
 * BaseEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
abstract class BaseEvent extends UI\Control implements IEvent
{

    /** Events properties */
    const PREFIX = 'event_';
    const SUFFIX = '.jstree';

    /** Sides */
    const SIDE_SERVER = 'server';
    const SIDE_CLIENT = 'client';

    /** Types */
    const TYPE_LOAD = 0;
    const TYPE_NODE = 1;

    /** @var callable */
    protected $callback;

    /** @var string */
    protected $side = self::SIDE_SERVER;

    /** @var array */
    protected $parameters = [];

    /** @var int */
    protected $type = self::TYPE_NODE;
    protected $refreshOnSuccess = TRUE;
    protected $refreshOnError = TRUE;

    /** @var string|NULL */
    protected $confirm = NULL;


    public function render()
    {
        $template = $this->template->setFile(__DIR__ . '/templates/event.latte');
        $template->side = $this->getSide();
        $template->eventRealname = $this->getRealname() . self::SUFFIX;
        $template->eventCallback = $this->getParent()->link('callback', [$this->getRealname()]);
        $template->parametersString = $this->getParametersString();
        $template->controlName = $this->getParent()->getControlPath();
        $template->refreshOnSuccess = $this->refreshOnSuccess;
        $template->refreshOnError = $this->refreshOnError;
        $template->joinTree = $this->getParent()->getOption('joinTree') === NULL ? 'null' : $this->getParent()->getOption('joinTree');
        $template->render();
    }


    /**
     * Set callback for event.
     * @param callable $callback
     * @return BaseEvent
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        $this->side = self::SIDE_SERVER;
        return $this;
    }


    /** @return callable */
    public function getCallback()
    {
        return $this->callback;
    }


    /** @return int */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set type of event.
     * @param int $type
     * @return BaseEvent
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }


    /** @inheritdoc */
    public function getRealname()
    {
        return substr($this->name, strlen(self::PREFIX));
    }


    public function getParametersString()
    {
        $parametersString = '{';
        foreach ($this->parameters as $key => $value) {
            $parametersString .= '\'' . $key . '\': ' . $value . ',';
        }

        $parametersString = rtrim($parametersString, ',') . '}';
        return $parametersString;
    }


    public function getSide()
    {
        return $this->side;
    }


    public function setSide($side)
    {
        $this->side = $side;
        return $this;
    }


    public function getConfirm()
    {
        return $this->confirm;
    }


    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
        return $this;
    }


}
