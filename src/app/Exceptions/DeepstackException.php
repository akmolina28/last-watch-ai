<?php

namespace App\Exceptions;

use App\DetectionEvent;
use Exception;

class DeepstackException extends Exception
{
    public static function deepstackError(DetectionEvent $event, string $error)
    {
        return new static('The Deepstack client returned an error for event ' . $event->id . ': ' . $error);
    }
}
