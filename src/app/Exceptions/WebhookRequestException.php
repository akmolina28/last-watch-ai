<?php


namespace App\Exceptions;

use Exception;

class WebhookRequestException extends Exception
{
    public static function fileMissingFromPayload()
    {
        return new static('The file key is missing from the Webhook request payload.');
    }
}
