<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Plugins;

/**
 * TypesPlugin.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class TypesPlugin extends BasePlugin
{

    /** @var array */
    private $types = [];


    /** Render. */
    public function render()
    {
        if (count($this->types) > 0) {
            $this->printBegin();
        }

        foreach ($this->types as $name => $content) {
            $this->printType($name, $content);
        }

        if (count($this->types) > 0) {
            $this->printEnd();
        }
    }


    /**
     * Register new type.
     * @param string $name
     * @param string $icon
     * @return Plugins\TypesPlugin
     */
    public function registerType($name, $icon)
    {
        $this->types[$name] = [
            'icon' => $icon,
        ];

        return $this;
    }


    /**
     * Print type.
     * @param string $name
     * @param array $content
     */
    private function printType($name, array $content)
    {
        echo '\'' . $name . '\': {' . PHP_EOL;
        echo 'icon: \'' . $content['icon'] . '\'' . PHP_EOL;
        echo '},' . PHP_EOL;
    }


}
