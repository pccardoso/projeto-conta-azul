<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EfiCreditCardService implements EfiPaymentGatewayInterface
{
    public function authenticate(): array
    {
        $response = Http::acceptJson()
            ->withBasicAuth(
                env('EFI_HOMOLOGACAO_CLIENTE_ID'),
                env('EFI_HOMOLOGACAO_CLIENTE_SECRET')
            )
            ->post(config('services.efi.domain_credit') . '/v1/authorize', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->status() !== 200) {
            Log::error('Erro ao autenticar na API EFI (cartão de crédito): ' . $response->body());
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
            Log::error('Erro ao gerar link de pagamento: Token de acesso não encontrado.');
            return [
                'error' => 'Erro ao gerar link de pagamento',
                'message' => 'Token de acesso não encontrado.',
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post(config('services.efi.domain_credit') . '/v1/charge/one-step/link', $dados);

        return $response->json();
    }
}
