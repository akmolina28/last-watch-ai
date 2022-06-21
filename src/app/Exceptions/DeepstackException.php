<?php

namespace App\Exceptions;

use Exception;
use App\DetectionEvent;

class DeepstackException extends Exception
{
    public static function deepstackError(DetectionEvent $event, string $error)
    {
        return new static('The Deepstack client returned an error for event '.$event->id.': '.$error);
    }
}
