<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Events;

/**
 * IEvent.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IEvent
{


    /** Render event. */
    public function render();


    /** @return callable */
    public function getCallback();


    /** @return int */
    public function getType();


    /**
     * Get name of real jstree event.
     * @return string
     */
    public function getRealname();
}
