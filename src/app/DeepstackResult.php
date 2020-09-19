<?php

namespace App;

use Illuminate\Support\Arr;

class DeepstackResult {

    public $response;

    public function __construct($response) {
        $this->response = json_decode($response);
    }

    public function getSuccess() {
        return $this->response->success;
    }

    public function getPredictions() {
        return $this->response->predictions;
    }
}
