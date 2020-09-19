<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepstackClient {
    /**
     * @var string
     */
    private $api_base_url;

    public function __construct() {
        $this->api_base_url = config('app.deepstack_base_url');
    }

    public function detection($image_path) {
        $url = $this->api_base_url . 'v1/vision/detection';

        $response = Http::attach(
            'image', file_get_contents($image_path), 'photo.jpg'
        )->post($url);

        return $response->body();
    }
}
