<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Rendering;

use Rathouz\DataTree\Components\DataTree;


/**
 * IRenderer.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IRenderer
{


    /**
     * Render data-tree component.
     * @param DataTree $dataTree
     */
    public function render(DataTree $dataTree);


}
