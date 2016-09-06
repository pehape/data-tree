<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
