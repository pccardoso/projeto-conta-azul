<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PeripheralFinancialReleases;
use App\Service\PipefyService;

class EfiPixService implements EfiPaymentGatewayInterface
{

    public function __construct(
        protected PipefyService $pipefyService
    ){}

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

        $listPix = dataget($data, 'pix', []);

        if(count($listPix)){
        
            foreach($listPix as $pix){
                
                $txid = data_get($pix, 'txid', null);
                
                if($txid){
                    $peripheralFinancialRelease = PeripheralFinancialReleases::where('txid_efi', $txid)->first();

                    if($peripheralFinancialRelease){
                    
                        $dataPayment = $this->getTixId($txid);

                        if(data_get($dataPayment, 'status', null) === 'CONCLUIDA'){
                        
                            $this->pipefyService->moveCard([
                                "cardId" => $peripheralFinancialRelease->id_card_pipefy,
                                "phaseId" => 343120341
                            ]);

                        }

                    }
                }


            }

        }

        return [];

    }
}
