<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\FinancialReleases;
use App\Service\ContaAzulService;
use App\Service\FinancialReleasesService;
use App\Enum\StatusFinancialEnum;
use Illuminate\Support\Facades\Log;
use App\Service\PipefyService;


#[Signature('app:validate-financial-command')]
#[Description('Command description')]
class ValidateFinancialCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        ContaAzulService $contaAzulService,
        FinancialReleasesService $financialReleasesService,
        PipefyService $pipefyService
    )
    {
        
        $listFinancial = FinancialReleases::whereIn('status', [
            StatusFinancialEnum::PENDENTE,
            StatusFinancialEnum::ATRASADO,
        ])
        ->whereNotNull('event')
        ->get();
        
        foreach ($listFinancial as $financial) {

            $dataEventFinancial = $contaAzulService->getEvent($financial->event, $financial->base_integration);

            if($dataEventFinancial){

                $statusEvent = data_get($dataEventFinancial, '0.status', null);
                $paidAmount = data_get($dataEventFinancial, '0.valor_pago', null);
                $datePayment = data_get($dataEventFinancial, '0.baixas.0.data_pagamento', null);


                if($statusEvent === StatusFinancialEnum::QUITADO->value){
                    $financial->update([
                        'status' => StatusFinancialEnum::QUITADO,
                        'amount_paid' => $paidAmount,
                        'payment_date' => $datePayment
                    ]);

                    //Enviar E-mail
                    $financialReleasesService->sendEmailBeneficiary($financial->id_card_pipefy, $financial);

                    //Mover o cartão para Quitado
                    $pipefyService->moveCard([
                        "cardId" => $financial->id_card_pipefy,
                        "phaseId" => 341980295
                    ]);

                }

            }

        }

    }
}
