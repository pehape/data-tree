<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Plugins;

/**
 * IPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IPlugin
{


    /** Render plugin. */
    public function render();


    /** Render configuration */
    public function renderConfiguration();


    /** Get short plugin name. */
    public function getShortname();


}
