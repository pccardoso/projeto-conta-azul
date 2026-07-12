<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{

    public function index()
    {
        Cache::store('redis')->put('user', 'teste', 600);
    }

    public function get()
    {
        $user = Cache::store('redis')->get('user');
        return $user;
    }

    public function testeReqEfi(Request $request)
    {
        $data = $request->all();
        Log::info('Dados recebidos na rota testeReqEfi: ' . json_encode($data));
        return response()->json($data);
    }

}
