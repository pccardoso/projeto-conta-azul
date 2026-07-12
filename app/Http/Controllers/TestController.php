<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

}
