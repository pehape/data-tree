<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Rendering;

use Pehape\DataTree\Components\DataTree;


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
