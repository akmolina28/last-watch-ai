<?php

namespace App\Exceptions;

use Exception;

class AutomationException extends Exception
{
    public static function automationFailure(string $message)
    {
        return new static('The automation failed or returned an unsuccessful result: '.$message);
    }
}
