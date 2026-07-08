<?php

namespace App\Contracts;

interface EfiPaymentGatewayInterface
{
    public function authenticate(): array;

    public function gerarPagamento(array $dados): array;
}
