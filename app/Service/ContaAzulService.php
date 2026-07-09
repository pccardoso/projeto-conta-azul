<?php

    namespace App\Service;

    use App\Models\Tokens;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use App\Enum\TypeIntegrationContaAzulEnum;

    class ContaAzulService{


        public function getProtocol(string $protocol, string $baseIntegracao){

            try{

                $tokenValidate = Tokens::where(
                    'id',
                    $baseIntegracao === TypeIntegrationContaAzulEnum::COBERTURA_TOTAL->value ? 2 : 1
                )->first();
                
                $getProtocolResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenValidate->access_token,
                ])->get(config('services.contaazul.domain') . '/protocolo/' . $protocol);

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

        public function getEvent(string $eventId, string $baseIntegracao){

            try{

                $tokenValidate = Tokens::where(
                    'id',
                    $baseIntegracao === TypeIntegrationContaAzulEnum::COBERTURA_TOTAL->value ? 2 : 1
                )->first();

                $getEventResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tokenValidate->access_token,
                ])->get(config('services.contaazul.domain') . '/financeiro/eventos-financeiros/'.$eventId.'/parcelas');

                Log::info('Resposta da API de obtenção do evento: ' . $getEventResponse->body());

                if($getEventResponse->status() === 200){
                    return $getEventResponse->json();
                }
                
            }catch(\Exception $e){
                throw new \Exception($e->getMessage());
            }

        }

    }