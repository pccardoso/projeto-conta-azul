<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enum\EfiPaymentMethodEnum;
use App\Service\Efi\EfiPaymentGatewayFactory;
use App\Http\Requests\Efi\CreateLinkCreditCardRequest;
use App\Http\Requests\Efi\CreatePixRequest;

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

    public function createPix(CreatePixRequest $request)
    {
        $gateway = EfiPaymentGatewayFactory::make(EfiPaymentMethodEnum::PIX);

        return response()->json($gateway->gerarPagamento($request->validated()));
    }

    public function getTixId(string $txid)
    {
        if(empty($txid)){
            return response()->json([
                "message" => "Atenção, é necessário informar o txid para consulta!"
            ], 400);
        }

        $gateway = EfiPaymentGatewayFactory::make(EfiPaymentMethodEnum::PIX);

        $dataTix = $gateway->getTixId($txid);

        if(count($dataTix) === 0){
            return response()->json([
                "message" => "Nenhuma cobrança encontrada para o txid informado"
            ], 404);
        }

        return response()->json($dataTix);
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
