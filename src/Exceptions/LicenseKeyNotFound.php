<?php

namespace Freemius\Laravel\Exceptions;

use Freemius\Laravel\Http\Throwable\NotFound;

class LicenseKeyNotFound extends \Exception implements NotFound
{
    public static function withKey(string $key): static {
        return new self("No license found for key {$key}");
    }
}