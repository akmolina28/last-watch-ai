<?php

namespace App;

use CURLFile;

class TelegramClient
{
    private $token;
    private $chat_id;

    public function __construct($token, $chat_id)
    {
        $this->token = $token;
        $this->chat_id = $chat_id;
    }

    public function sendPhoto($photo_path)
    {
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

        $ret = curl_exec($ch);

        if (!$ret) {
            return curl_error($ch);
        }
        return $ret;
    }
}
