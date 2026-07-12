<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PeripheralFinancialReleases\StoreRequest;
use App\Service\PeripheralFinancialReleases\PeripheralFinancialReleasesService;

class PeripheralFinancialReleasesController extends Controller
{

    public function __construct(
        private PeripheralFinancialReleasesService $peripheralFinancialReleasesService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function showByIdCardPipefy(int $id_card_pipefy)
    {
        $response = $this->peripheralFinancialReleasesService->getPeripheralFinancialReleaseByIdCardPipefy($id_card_pipefy);

        if(count($response)){
            return response()->json([
                'message' => 'Liberação financeira periférica encontrada com sucesso',
                'data' => $response
            ], 200);
        }else{
            return response()->json([
                'message' => 'Nenhuma liberação financeira periférica encontrada'
            ], 404);
        }
    }

    public function showByTxidEfi(string $txid_efi)
    {
        $response = $this->peripheralFinancialReleasesService->getPeripheralFinancialReleaseByTxidEfi($txid_efi);

        if(count($response)){
            return response()->json([
                'message' => 'Liberação financeira periférica encontrada com sucesso',
                'data' => $response
            ], 200);
        }else{
            return response()->json([
                'message' => 'Nenhuma liberação financeira periférica encontrada'
            ], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        
        $response = $this->peripheralFinancialReleasesService->createPeripheralFinancialRelease($request->validated());

        if($response){
            return response()->json([
                'message' => 'Liberação financeira periférica criada com sucesso',
                'data' => $response
            ], 201);
        }else{
            return response()->json([
                'message' => 'Erro ao criar liberação financeira periférica'
            ], 500);
        }

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
