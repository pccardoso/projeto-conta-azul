<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enum\EfiPaymentMethodEnum;
use App\Service\Efi\EfiPaymentGatewayFactory;
use App\Http\Requests\Efi\CreateLinkCreditCardRequest;

class EfiApiController extends Controller
{

    public function authenticate(string $typeMethod)
    {
        $method = EfiPaymentMethodEnum::from(strtoupper($typeMethod));
        $gateway = EfiPaymentGatewayFactory::make($method);

        return response()->json($gateway->authenticate());
    }

    public function createLinkCreditCard(CreateLinkCreditCardRequest $request)
    {
        $gateway = EfiPaymentGatewayFactory::make(EfiPaymentMethodEnum::CREDIT_CARD);

        return response()->json($gateway->gerarPagamento($request->validated()));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
