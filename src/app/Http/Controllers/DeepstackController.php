<?php

namespace App\Http\Controllers;

class DeepstackController extends Controller
{
    public function showObjectClasses()
    {
        return config('deepstack.object_classes');
    }
}
