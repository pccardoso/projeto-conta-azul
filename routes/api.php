<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokensController;
use App\Http\Controllers\FinancialReleasesController;
use App\Http\Controllers\ContaAzulController;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\EfiApiController;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('tokens')->group(function () {
        Route::get('get-token', [TokensController::class, 'getToken']);
    });

    Route::prefix('financial-releases')->group(function () {
        Route::post('/', [FinancialReleasesController::class, 'store']);
        Route::get('/get-beneficiary/{idCardFinancial}', [FinancialReleasesController::class, 'getArrayBeneficiary']);
        Route::delete('/{id}/{type}', [FinancialReleasesController::class, 'destroy']);
    });

    Route::prefix('conta-azul')->group(function () {
        Route::get('get-protocol/{protocol}/{baseIntegracao}', [ContaAzulController::class, 'getProtocol']);
        Route::get('get-event/{eventId}', [ContaAzulController::class, 'getEvent']);
    });

    Route::prefix('efi-api')->group(function () {
        Route::post('authenticate/{typeMethod}', [EfiApiController::class, 'authenticate']);
        Route::post('create-link-credit-card', [EfiApiController::class, 'createLinkCreditCard']);
    });

    Route::get('teste', function () {
        return response()->json(['message' => 'API is working!']);
    });

});



Route::prefix('auth-user')->group(function () {
    Route::post('login', [AuthUserController::class, 'login'])->name('login');
});