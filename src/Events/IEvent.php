<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Events;

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


    /** @return string */
    public function getSide();


    /** @return string */
    public function getConfirm();


    /**
     * Get name of real jstree event.
     * @return string
     */
    public function getRealname();


}
