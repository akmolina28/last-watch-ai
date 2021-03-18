<?php

namespace App;

use CURLFile;

class TelegramClient
{
    private $token;
    private $chat_id;

    protected $error;

    public function __construct($token, $chat_id)
    {
        $this->token = $token;
        $this->chat_id = $chat_id;
    }

    public function getError()
    {
        return $this->error;
    }

    public function sendPhoto($photo_path)
    {
        $this->error = null;

        $url = 'https://api.telegram.org/bot'.$this->token.'/sendPhoto?chat_id='.$this->chat_id;

        $post_fields = [
            'chat_id' => $this->chat_id,
            'photo' => new CURLFile($photo_path),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:multipart/form-data',
        ]);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $response = curl_exec($ch);

        if (! $response) {
            $this->error = curl_error($ch);
            return false;
        }

        $responseJson = json_decode($response);

        if (!$responseJson->ok) {
            $this->error = 'The telegram service did not return successfully: '.$response;
            return false;
        }

        return true;
    }
}
