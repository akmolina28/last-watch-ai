<?php

namespace App\Mocks;

use App\DeepstackClientInterface;

class FakeDeepstackClient implements DeepstackClientInterface
{
    public function detection($image_path)
    {
        // @codingStandardsIgnoreLine
        return '{"success":true,"predictions":[{"confidence":0.9995428,"label":"person","y_min":95,"x_min":295,"y_max":523,"x_max":451},{"confidence":0.9994912,"label":"person","y_min":99,"x_min":440,"y_max":531,"x_max":608},{"confidence":0.9990447,"label":"dog","y_min":358,"x_min":647,"y_max":539,"x_max":797}]}';
    }
}
