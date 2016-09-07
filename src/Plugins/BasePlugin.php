<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Plugins;

use Nette\Application\UI;
use Pehape\DataTree\Components;

/**
 * BasePlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
abstract class BasePlugin extends UI\Control implements IPlugin
{

    /** Scopes */
    const PREFIX = 'plugin_';
    const SCOPE_INNER = 0;
    const SCOPE_OUTER = 1;

    /** @var int */
    protected $scope;

    /** @var Components\DataTree */
    protected $dataTree;
    
    public function __construct(Components\DataTree $dataTree)
    {
        parent::__construct();
        $this->dataTree = $dataTree;
    }

    /** @return string */
    public function getShortname()
    {
        return substr($this->name, strlen(self::PREFIX));
    }


    /**
     * Set plugin scope.
     * @param type $scope
     * @return $scope
     */
    public function setScope($scope)
    {
        $this->scope = (int) $scope;
    }


    /** @return int */
    public function getScope()
    {
        return $this->scope;
    }


    /** Print begin part of plugin. */
    protected function printBegin()
    {
        echo $this->getShortname() . ': {' . PHP_EOL;
    }


    /** Print end part of plugin. */
    protected function printEnd()
    {
        echo '},' . PHP_EOL;
    }


}
