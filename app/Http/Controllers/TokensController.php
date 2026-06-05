<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\TokensService;


class TokensController extends Controller
{

    public function __construct(
        protected TokensService $tokensService
    ){}

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
        //
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

    /**
     * Retornar token válido da integração com Conta Azul
     */

    public function getToken()
    {
        $token = $this->tokensService->acessToken();

        if($token) {
            return response()->json([
                'id_token' => $token->id_token,
                'access_token' => $token->access_token,
                'refresh_token' => $token->refresh_token,
                'updated_at' => $token->updated_at,
            ]);
        } else {
            return response()->json(['message' => 'Token not found'], 404);
        }
    }
}
