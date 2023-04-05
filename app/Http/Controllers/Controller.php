<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    public function index(Request $request)
    {
        // mengambil token dari header Authorization
        $token = JWTAuth::parseToken()->getToken();

        // mendekode token
        $payload = JWTAuth::decode($token); // atau bisa langsung getPayload
        // $payload = JWTAuth::getPayload($token);

        $res = [
            'response' => true,
            'data' => $request->user(), // menampilkan data user sesuai token dari header
            'payload' => $payload
        ];
        return response()->json($res);
    }
}
