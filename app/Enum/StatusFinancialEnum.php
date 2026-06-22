<?php

namespace App\Enum;

enum StatusFinancialEnum: string
{
    case ABERTO = 'ABERTO';
    case PENDENTE = 'PENDENTE';
    case PAGO = 'PAGO';
    case QUITADO = 'QUITADO';
    case ATRASADO = 'ATRASADO';
}
