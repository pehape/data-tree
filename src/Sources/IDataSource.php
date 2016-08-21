<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Sources;

/**
 * IDataSource.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IDataSource
{


    /**
     * Get nodes for the DataTree.
     * @param array $conditions
     * @return mixed
     */
    public function getNodes(array $conditions);


}
