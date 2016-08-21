<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Localization;

use Nette\Localization\ITranslator;


/**
 * Untranslation.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class Untranslation implements ITranslator
{


    /**
     * Returns original message. Nothing is translated.
     * @param string $message
     * @param int|NULL $count
     * @return string
     */
    public function translate($message, $count = NULL)
    {
        return $message;
    }


}
