<?php

namespace Freemius\Laravel\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidCustomer extends Exception
{
    public static function notYetCreated(Model $owner): InvalidCustomer
    {
        return new InvalidCustomer(class_basename($owner) . ' is not a Freemius customer yet.');
    }
}