<?php

/**
 * This file is part of the Rathouz libraries (http://rathouz.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Rathouz\DataTree\Localization;

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
