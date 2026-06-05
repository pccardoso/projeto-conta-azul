<?php

namespace App;

enum StatusFinancialEnum: string
{
    case ABERTO = 'aberto';
    case PENDENTE = 'pendente';
    case PAGO = 'pago';
    case BAIXADO = 'baixado';
}
