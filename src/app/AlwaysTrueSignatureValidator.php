<?php

namespace App;

use \Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class AlwaysTrueSignatureValidator implements SignatureValidator {

    public function isValid(\Illuminate\Http\Request $request, \Spatie\WebhookClient\WebhookConfig $config): bool
    {
        return true;
    }
}
