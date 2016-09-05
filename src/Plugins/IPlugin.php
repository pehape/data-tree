<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Plugins;

/**
 * IPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IPlugin
{


    /** Render plugin. */
    public function render();


    /** Get plugin scope. */
    public function getScope();


    /** Get short plugin name. */
    public function getShortname();
}
