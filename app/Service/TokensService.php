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
        
                $tokenRecord = Tokens::orderBy('id', 'asc')->get();

                Log::info('Dados para validação de token: ' . json_encode($tokenRecord));

                Log::info('*** VALIDANDO TOKEN MEU VEÍCULO');

                $response = Http::asForm()
                    ->withBasicAuth(config('services.contaazul.meu_veiculo.client_id'), config('services.contaazul.meu_veiculo.client_secret'))
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
                    ->withBasicAuth(config('services.contaazul.cobertura_total.client_id'), config('services.contaazul.cobertura_total.client_secret'))
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

            $tokens = Tokens::orderBy('id', 'asc')->get();

            return [
                "MEU_VEICULO" => $tokens[0],
                "COBERTURA_TOTAL" => $tokens[1],
            ];

        }

    }