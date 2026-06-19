<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AuthUser\LoginRequest;
use App\Service\AuthUserService;

class AuthUserController extends Controller
{

    public function __construct(
        protected AuthUserService $authUserService
    ){}
    

    /**
     * Autenticação de Usuários
     */
    public function login(LoginRequest $request){

        return response()->json($this->authUserService->login($request->validated()));

    }

}
