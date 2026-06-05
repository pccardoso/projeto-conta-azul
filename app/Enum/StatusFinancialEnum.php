<?php

namespace App\Enum;

enum StatusFinancialEnum: string
{
    case ABERTO = 'aberto';
    case PENDENTE = 'pendente';
    case PAGO = 'pago';
    case BAIXADO = 'baixado';
}
