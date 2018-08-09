<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Mappers;

/**
 * IDataMapper.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
interface IDataMapper extends IMapper
{


    /**
     * Apply mapping.
     * @param mixed $data
     * return mixed
     */
    public function applyMapping($data);


}
