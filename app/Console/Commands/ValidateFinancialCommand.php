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
use Carbon\Carbon;


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

                $dueDateExpense = data_get($dataEventFinancial, '0.data_vencimento');
                $originalDueDate = $financial->due_date;

                //Validar se o pagamento foi prorrogado
                if (
                    $dueDateExpense &&
                    $originalDueDate &&
                    $dueDateExpense !== $originalDueDate &&
                    Carbon::parse($dueDateExpense)->greaterThan(Carbon::parse($originalDueDate)) &&
                    $statusEvent === StatusFinancialEnum::PENDENTE->value
                ) {

                    Log::info('Pagamento foi prorrogado: '.json_encode($financial));

                    //Aplicar etiqueta
                    $pipefyService->updateLabel([
                        "cardId" => $financial->id_card_pipefy,
                        "labelIds" => [317912524]
                    ]);

                    //Atualizar o due date no card do pipefy
                    $pipefyService->updateCard([
                        "cardId" => $financial->id_card_pipefy,
                        "fields" => [
                            [
                                "field_id" => "prazo_para_pagamento",
                                "field_value" => Carbon::parse($dueDateExpense, 'America/Fortaleza')
                                                    ->setTime(23, 59, 59)
                                                    ->toIso8601String()
                            ]
                        ]
                    ]);

                    $financial->update([
                        'due_date' => $dueDateExpense,
                        'due_date_expected' => $dueDateExpense
                    ]);



                }


                if($statusEvent === StatusFinancialEnum::QUITADO->value){

                    //Validando condicional de antecipado

                    if ($datePayment && $dueDateExpense
                        && Carbon::parse($datePayment)->lessThan(Carbon::parse($dueDateExpense))) {

                        Log::info('Pagamento foi antecipado: '.json_encode($financial));
                        
                        $pipefyService->updateLabel([
                            "cardId" => $financial->id_card_pipefy,
                            "labelIds" => [317912526]
                        ]);

                    }

                    $financial->update([
                        'status' => StatusFinancialEnum::QUITADO,
                        'amount_paid' => $paidAmount,
                        'payment_date' => $datePayment
                    ]);

                    //Enviar E-mail
                    $financialReleasesService->sendEmailBeneficiary($financial->id_card_pipefy, $financial);

                    
                    //Obter dados do cartão
                    $dataCardFinancial = $pipefyService->getCard($financial->id_card_pipefy);

                    //Obter campos do cartão
                    $fieldsCards = data_get($dataCardFinancial, 'fields', []);

                    //Validando se há o campo de Nota fiscal
                    $fieldAttachmentNFe = collect($fieldsCards)->first(function ($field) {
                        return data_get($field, 'name') === 'Nota fiscal'
                            && data_get($field, 'value') !== '[]';
                    });

                    Log::info('fieldAttachmentNFe: '.json_encode($fieldAttachmentNFe));

                    if(!$fieldAttachmentNFe){

                        //Mover o cartão para "Pgt Efetuado - Aguardando NF-e"
                        $pipefyService->moveCard([
                            "cardId" => $financial->id_card_pipefy,
                            "phaseId" => 343533611
                        ]);
                        
                    }else{

                        //Mover o cartão para "Pagamento Efetuado"
                        $pipefyService->moveCard([
                            "cardId" => $financial->id_card_pipefy,
                            "phaseId" => 341980295
                        ]);

                    }

                    //atualizar a data de pagamento

                    $pipefyService->updateCard([
                        "cardId" => $financial->id_card_pipefy,
                        "fields" => [
                            [
                                "field_id" => "data_do_pagamento",
                                "field_value" => $datePayment
                            ],
                            [
                                "field_id" => "valor_do_pagamento",
                                "field_value" => $paidAmount
                            ]
                        ]
                    ]);

                }

                if($statusEvent === StatusFinancialEnum::ATRASADO->value){
                    $financial->update([
                        'status' => StatusFinancialEnum::ATRASADO,
                    ]);

                    //Mover o cartão para fase Atrasado
                    $pipefyService->moveCard([
                        "cardId" => $financial->id_card_pipefy,
                        "phaseId" => 343487714
                    ]);
                }

            }

        }

    }
}
