<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function catchAll()
    {
        return response()->json(['message' => 'Not Found.'], 404);
    }
}
