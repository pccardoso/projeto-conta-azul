<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ContaAzulService;

class ContaAzulController extends Controller
{

    public function __construct(
        protected ContaAzulService $contaAzulService
    ){}

    /**
     * Obter informações do protocolo do Conta Azul
    */

    
    public function getProtocol(string $protocol, string $baseIntegracao){

        if(empty($protocol)){
            return response()->json(['error' => 'O protocolo é obrigatório.'], 400);
        }

        return $this->contaAzulService->getProtocol($protocol, $baseIntegracao);
    }

    /**
     * Obter informações do evento do Conta Azul
     */
    public function getEvent(string $eventId){

        if(empty($eventId)){
            return response()->json(['error' => 'O evento é obrigatório.'], 400);
        }

        return $this->contaAzulService->getEvent($eventId);
    }

}
