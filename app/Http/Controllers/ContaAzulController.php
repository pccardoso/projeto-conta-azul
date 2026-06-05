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

    
    public function getProtocol(string $protocol){
        return $this->contaAzulService->getProtocol($protocol);
    }

    /**
     * Obter informações do evento do Conta Azul
     */
    public function getEvent(string $eventId){
        return $this->contaAzulService->getEvent($eventId);
    }

}
