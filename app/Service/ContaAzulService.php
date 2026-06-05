<?php

    namespace App\Service;

    use App\Models\Tokens;
    use Illuminate\Support\Facades\Http;

    class ContaAzulService{


        public function getProtocol(string $protocol){

            try{

                $tokenValidate = Tokens::first();
                
                $getProtocolResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenValidate->access_token,
                ])->get(env('CONTA_AZUL_DOMAIN') . '/protocolo/' . $protocol);

                if($getProtocolResponse->status() === 200){
                    return $getProtocolResponse->json();
                }

                if($getProtocolResponse->status() === 400){
                    return $getProtocolResponse->json();
                }

            }catch(\Exception $e){
                throw new \Exception($e->getMessage());
            }

        }

        public function getEvent(string $eventId){

            try{

                $tokenValidate = Tokens::first();

                $getEventResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenValidate->access_token,
                ])->get(env('CONTA_AZUL_DOMAIN') . '/financeiro/eventos-financeiros/'.$eventId.'/parcelas');

                if($getEventResponse->status() === 200){
                    return $getEventResponse->json();
                }
                
            }catch(\Exception $e){
                throw new \Exception($e->getMessage());
            }

        }

    }