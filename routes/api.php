<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokensController;
use App\Http\Controllers\FinancialReleasesController;
use App\Http\Controllers\ContaAzulController;

Route::prefix('tokens')->group(function () {
    Route::get('get-token', [TokensController::class, 'getToken']);
});

Route::prefix('financial-releases')->group(function () {
    Route::post('/', [FinancialReleasesController::class, 'store']);
});

Route::prefix('conta-azul')->group(function () {
    Route::get('get-protocol/{protocol}', [ContaAzulController::class, 'getProtocol']);
    Route::get('get-event/{eventId}', [ContaAzulController::class, 'getEvent']);
});