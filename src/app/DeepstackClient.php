<?php

namespace App;

use Illuminate\Support\Facades\Http;

class DeepstackClient implements DeepstackClientInterface
{
    public string $api_base_url;

    /**
     * DeepstackClient constructor.
     * @param string $api_base_url
     */
    public function __construct(string $api_base_url)
    {
        $this->api_base_url = rtrim($api_base_url, '/');
    }

    public function detection($imageFileContents)
    {
        $url = $this->api_base_url.'/v1/vision/detection';

        $response = Http::attach(
            'image',
            $imageFileContents,
            'photo.jpg'
        )->post($url);

        return $response->body();
    }
}
