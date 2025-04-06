<?php

namespace Freemius\Laravel\Exceptions;

use Illuminate\Validation\Validator;
use Freemius\Laravel\Http\Throwable\BadRequest;

class MalformedDataError extends \Exception implements BadRequest
{
    public static function forLicenseKey(Validator $validator): static
    {
        return new static('LicenseKey key data is malformed:' . $validator->errors());
    }
}