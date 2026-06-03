<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokensController;

Route::prefix('tokens')->group(function () {
    Route::get('get-token', [TokensController::class, 'getToken']);
});