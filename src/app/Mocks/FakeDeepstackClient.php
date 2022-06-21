<?php

namespace App\Mocks;

use App\DeepstackClientInterface;

class FakeDeepstackClient implements DeepstackClientInterface
{
    // @codingStandardsIgnoreLine
    protected $json_response = '{"success":true,"predictions":[{"confidence":0.9995428,"label":"person","y_min":95,"x_min":295,"y_max":523,"x_max":451},{"confidence":0.9994912,"label":"person","y_min":99,"x_min":440,"y_max":531,"x_max":608},{"confidence":0.9990447,"label":"dog","y_min":358,"x_min":647,"y_max":539,"x_max":797}]}';

    public function __construct($json_response = null)
    {
        if ($json_response)
        {
            $this->json_response = $json_response;
        }
    }

    public function detection($image_path)
    {
        return $this->json_response;
    }
}
