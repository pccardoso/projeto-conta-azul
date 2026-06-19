<?php

    namespace App\Service;
    use App\Models\Tokens;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;


    class TokensService
    {
    
        public function validateToken()
        {
        
            try{
        
                $tokenRecord = Tokens::orderBy('created_at', 'asc')->get();

                Log::info('Dados para validação de token: ' . json_encode($tokenRecord));

                Log::info('*** VALIDANDO TOKEN MEU VEÍCULO');

                $response = Http::asForm()
                    ->withBasicAuth(env('CONTA_AZUL_CLIENT_ID'), env('CONTA_AZUL_CLIENT_SECRET'))
                    ->post('https://auth.contaazul.com/oauth2/token', [
                        'refresh_token' => $tokenRecord[0]->refresh_token,
                        'grant_type' => 'refresh_token',
                    ]);

                Log::info('REPOSTA CONTA AZUL MEU VEÍCULO: ' . $response->body());

                if($response->status() === 200) {
                    
                    $tokenRecord[0]->update([
                        'id_token' => $response->json()['id_token'],
                        'access_token' => $response->json()['access_token'],
                        'refresh_token' => $response->json()['refresh_token'],
                    ]);
                } else {
                    Log::error('Erro ao validar token: ' . $response->body());
                }

                Log::info('*** VALIDANDO TOKEN COBERTURA TOTAL');

                $response = Http::asForm()
                    ->withBasicAuth(env('CONTA_AZUL_CLIENT_ID_CE'), env('CONTA_AZUL_CLIENT_SECRET_CE'))
                    ->post('https://auth.contaazul.com/oauth2/token', [
                        'refresh_token' => $tokenRecord[1]->refresh_token,
                        'grant_type' => 'refresh_token',
                    ]);

                Log::info('RESPOSTA CONTA AZUL COBERTURA TOTAL: ' . $response->body());

                if($response->status() === 200) {
                    
                    $tokenRecord[1]->update([
                        'id_token' => $response->json()['id_token'],
                        'access_token' => $response->json()['access_token'],
                        'refresh_token' => $response->json()['refresh_token'],
                    ]);
                    
                } else {
                    Log::error('Erro ao validar token: ' . $response->body());
                }

            } catch (\Exception $e) {
                Log::error('Erro ao validar token: ' . $e->getMessage());
            }

        }

        public function acessToken()
        {
            return Tokens::first();
        }

    }