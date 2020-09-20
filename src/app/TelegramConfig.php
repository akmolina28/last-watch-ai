<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramConfig extends Model
{
    protected $fillable = ['name', 'token', 'chat_id'];
}
