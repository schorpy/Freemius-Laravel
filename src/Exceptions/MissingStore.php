<?php

namespace Freemius\Laravel\Exceptions;

use Exception;

class MissingStore extends Exception
{
    public static function notConfigured(): MissingStore
    {
        return new MissingStore('The Freemius store was not configured.');
    }
}