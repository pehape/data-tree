<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Mappers;

use Pehape\DataTree\Exceptions;


/**
 * DatabaseMapper.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DatabaseMapper implements IDataMapper
{

    /** @var array */
    private $defaultMapping = [
        'id' => 'id',
        'ancestor' => 'parent',
        'name' => 'text',
        'type' => 'type',
    ];

    /** @var array List of required mapping keys */
    private $requiredMappingKeys = ['id', 'parent', 'text'];

    /** @var array */
    private $mapping;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mapping = $this->defaultMapping;
    }


    /**
     * Apply mapping.
     * @param mixed $data
     * @return array
     * @throws Exceptions\UnvalidDataMappingException
     */
    public function applyMapping($data)
    {
        $mappedData = [];
        foreach ($data as $dataItem) {
            $mappedItem = [];
            foreach ($this->getMapping() as $key => $replacement) {
                if (isset($dataItem->$key) === FALSE) {
                    throw new Exceptions\UnvalidDataMappingException($key);
                }

                $mappedItem[$replacement] = $dataItem->$key;
            }

            if ($mappedItem['parent'] === 0) {
                $mappedItem['parent'] = '#';
            }

            $mappedData[] = $mappedItem;
        }

        return $mappedData;
    }


    /** @return array */
    public function getMapping()
    {
        return $this->mapping;
    }


    /**
     * Set mapping.
     * @param array $mapping
     * @throws Exceptions\UnvalidDataMappingException
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $this->validateMapping($mapping);
    }


    /**
     * Validate mapping.
     * @param array $mapping
     * @return array
     * @throws Exceptions\UnvalidDataMappingException
     */
    private function validateMapping(array $mapping)
    {
        foreach ($this->requiredMappingKeys as $requiredKey) {
            if (in_array($requiredKey, $mapping) === FALSE) {
                throw new Exceptions\IncompleteDataMappingException($this->requiredMappingKeys, $mapping);
            }
        }

        return $mapping;
    }


    /** @return int */
    private function getRootId(array $data)
    {
        $parents = [];
        foreach ($data as $item) {
            $parents[] = $item->ancestor;
        }

        return (empty($parents) === FALSE) ? min($parents) : 0;
    }


}
