<?php

namespace App\Http\Controllers;

class ErrorController extends Controller
{
    public function catchAll()
    {
        return response()->json(['message' => 'Not Found.'], 404);
    }
}
