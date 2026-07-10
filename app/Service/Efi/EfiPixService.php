<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
