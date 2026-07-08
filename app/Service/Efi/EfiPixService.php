<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EfiPixService implements EfiPaymentGatewayInterface
{
    private function certificadoPath(): string
    {
        return storage_path('app/private/efi/' . env('EFI_PIX_CERTIFICADO_FILENAME'));
    }

    public function authenticate(): array
    {
        $response = Http::acceptJson()
            ->withOptions([
                'cert' => $this->certificadoPath(),
            ])
            ->withBasicAuth(
                env('EFI_HOMOLOGACAO_CLIENTE_ID'),
                env('EFI_HOMOLOGACAO_CLIENTE_SECRET')
            )
            ->post(env('EFI_DOMAIN') . '/v1/authorize', [
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
            'cert' => $this->certificadoPath(),
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post(env('EFI_DOMAIN') . '/v1/cob', $dados);

        return $response->json();
    }
}
