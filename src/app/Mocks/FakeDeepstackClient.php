<?php

namespace App\Mocks;

use App\DeepstackClientInterface;

class FakeDeepstackClient implements DeepstackClientInterface {
    public function detection($image_path) {
        return '{"success":true,"predictions":[{"confidence":0.99816895,"label":"person","y_min":128,"x_min":162,"y_max":381,"x_max":251},{"confidence":0.9918992,"label":"car","y_min":47,"x_min":334,"y_max":104,"x_max":446},{"confidence":0.98241407,"label":"car","y_min":73,"x_min":120,"y_max":169,"x_max":201}]}';
    }
}
