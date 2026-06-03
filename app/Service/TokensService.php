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
        
                $tokenRecord = Tokens::first();

                Log::info('Configuração Token encontrada: ' . $tokenRecord);

                $response = Http::asForm()
                    ->withBasicAuth(env('CONTA_AZUL_CLIENT_ID'), env('CONTA_AZUL_CLIENT_SECRET'))
                    ->post('https://auth.contaazul.com/oauth2/token', [
                        'refresh_token' => $tokenRecord->refresh_token,
                        'grant_type' => 'refresh_token',
                    ]);

                Log::info('Resposta da API de validação de token: ' . $response->body());

                if($response->status() === 200) {
                    
                    $tokenRecord->update([
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