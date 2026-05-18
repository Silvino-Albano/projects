<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zona;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => Zona::all(),
        ]);
    }
}