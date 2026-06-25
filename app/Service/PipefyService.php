<?php

    namespace App\Service;

    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class PipefyService {


        public function updateCard(array $data) {

            Log::info('Dados para atualização do card: '.json_encode($data));

            $responseUpdate = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->put('https://integration-pipefy.mundoevogard.com/pipefy/update-card', $data);

            Log::info('Resposta da API de atualização do card: '.json_encode($responseUpdate->body()));

            if($responseUpdate->status() === 200){
                return $responseUpdate->json();
            }

            return false;

        }

        public function moveCard(array $data) {
            
            $responseMove = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://integration-pipefy.mundoevogard.com/pipefy/move-card', $data);

            Log::info('Resposta da API de atualização do card: '.json_encode($responseMove->body()));

            if($responseMove->status() === 200){
                return $responseMove->json();
            }

            return false;

        }

        public function updateLabel(array $data){

            $responseUpdateLabel = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->put('https://integration-pipefy.mundoevogard.com/pipefy/update-card-label', $data);

            if($responseUpdateLabel->status() === 200){
                return $responseUpdateLabel->json();
            }

            return false;

        }

        public function getCard(int $idCard){

            $responseGetCard = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/'.$idCard);

            if($responseGetCard->status() === 200){
                return $responseGetCard->json();
            }

            return false;
        }
    }