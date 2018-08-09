<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Exceptions;

// @codingStandardsIgnoreStart

class DataSourceException extends \RuntimeException {}

class UnknownSourceTableException extends \RuntimeException
{


    /** @param string $tableName */
    public function __construct($tableName)
    {
        $message = 'Source table "' . $tableName . '" does not exist.';
        parent::__construct($message);
    }


}


class IncompleteDataMappingException extends \RuntimeException
{


    /**
     * @param array $requiredField
     * @param array $currentFields
     */
    public function __construct(array $requiredField, array $currentFields)
    {
        $message = 'Required mapping keys are [' . implode(',', $requiredField) . '], given keys [' . implode(',', $currentFields) . '].';
        parent::__construct($message);
    }


}


class UnvalidDataMappingException extends \RuntimeException
{


    /** @param string $key */
    public function __construct($key)
    {
        $message = 'Source data does not contain key "' . $key . '".';
        parent::__construct($message);
    }


}

class MissingPluginClassException extends \RuntimeException {}

class UnvalidPluginClassException extends \RuntimeException {}

class MissingEventClassException extends \RuntimeException {}

class UnvalidEventClassException extends \RuntimeException {}

// @codingStandardsIgnoreEnd
