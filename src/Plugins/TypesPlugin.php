<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Plugins;

use Pehape\Tools;


/**
 * TypesPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class TypesPlugin extends BasePlugin
{


    /**
     * Register new type.
     * @param string $name
     * @param string $icon
     * @param array $validChildren
     * @return Plugins\TypesPlugin
     */
    public function registerType($name, $icon, array $validChildren = NULL)
    {
        if (($this->options->$name instanceof Tools\Objects\JObject) === FALSE) {
            $this->options->$name = new Tools\Objects\JObject('');
        }

        $type = $this->options->$name;
        $type->icon = $icon;
        if ($validChildren !== NULL) {
            $type->valid_children = $validChildren;
        }

        return $this;
    }


}
