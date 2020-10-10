<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeepstackController extends Controller
{
    public function showObjectClasses()
    {
        return config('deepstack.object_classes');
    }
}
