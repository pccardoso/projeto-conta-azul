<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PeripheralFinancialReleases;

class EfiPixService implements EfiPaymentGatewayInterface
{

    public function authenticate(): array
    {
        $response = Http::acceptJson()
            ->withOptions([
                'cert' => config('services.efi.certificate_path'),
            ])
            ->withBasicAuth(
                config('services.efi.client_id'),
                config('services.efi.client_secret')
            )
            ->post(config('services.efi.domain_pix') . '/oauth/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->status() !== 200) {
            Log::error('Erro ao autenticar na API EFI (Pix): ' . $response->body());
            return [
                'error' => 'Erro ao autenticar na API EFI',
                'message' => $response->body(),
            ];
        }

        return $response->json();
    }

    public function gerarPagamento(array $dados): array
    {
        $authResponse = $this->authenticate();
        $accessToken = $authResponse['access_token'] ?? null;

        if (!$accessToken) {
            Log::error('Erro ao gerar cobrança Pix: Token de acesso não encontrado.');
            return [
                'error' => 'Erro ao gerar cobrança Pix',
                'message' => 'Token de acesso não encontrado.',
            ];
        }

        $response = Http::withOptions([
            'cert' => config('services.efi.certificate_path'),
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post(config('services.efi.domain_pix') . '/v2/cob', $dados);

        return $response->json();
    }

    public function getTixId(string $txid): array
    {
        $authResponse = $this->authenticate();
        $accessToken = $authResponse['access_token'] ?? null;

        if (!$accessToken) {
            Log::error('Erro ao consultar cobrança Pix: Token de acesso não encontrado.');
            return [
                'error' => 'Erro ao consultar cobrança Pix',
                'message' => 'Token de acesso não encontrado.',
            ];
        }

        $response = Http::withOptions([
            'cert' => config('services.efi.certificate_path'),
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->get(config('services.efi.domain_pix') . '/v2/cob/' . $txid);

        if($response->status() !== 200) return [];

        return $response->json();
    }

    public function getPaymentWebhookData(array $data): array{

        $listPix = data_get($data, 'pix', []);

        if(count($listPix)){

            $responseTxid = [];
        
            foreach($listPix as $pix){
                
                $txid = data_get($pix, 'txid', null);

                Log::info('txid recebido no webhook: '.json_encode($txid));
                
                if($txid){

                    $peripheralFinancialRelease = PeripheralFinancialReleases::where('txid_efi', $txid)->first();

                    if($peripheralFinancialRelease){
                    
                        $dataPayment = $this->getTixId($txid);

                        if(count($dataPayment)) $responseTxid[] = $dataPayment;

                    }
                }

            }

            Log::info('Resposta da API de atualização do card: '.json_encode($responseTxid));

            return $responseTxid;

        }

        Log::info('Nenhum Pix encontrado no webhook recebido: '.json_encode($data));
        return [];

    }
}
