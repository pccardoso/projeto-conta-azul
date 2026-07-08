<?php

namespace App\Service\Efi;

use App\Contracts\EfiPaymentGatewayInterface;
use App\Enum\EfiPaymentMethodEnum;

class EfiPaymentGatewayFactory
{
    public static function make(EfiPaymentMethodEnum $method): EfiPaymentGatewayInterface
    {
        return match ($method) {
            EfiPaymentMethodEnum::CREDIT_CARD => new EfiCreditCardService(),
            EfiPaymentMethodEnum::PIX => new EfiPixService(),
        };
    }
}
