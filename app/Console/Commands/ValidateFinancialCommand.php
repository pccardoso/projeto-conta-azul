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


#[Signature('app:validate-financial-command')]
#[Description('Command description')]
class ValidateFinancialCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        ContaAzulService $contaAzulService,
        FinancialReleasesService $financialReleasesService
    )
    {
        $listFinancial = FinancialReleases::where([
            ['status', '=', StatusFinancialEnum::PENDENTE],
        ])
        ->whereNotNull('event')
        ->get();
        
        foreach ($listFinancial as $financial) {

            $dataEventFinancial = $contaAzulService->getEvent($financial->event);

            if($dataEventFinancial){

                $statusEvent = data_get($dataEventFinancial, '0.status', null);


                if($statusEvent === StatusFinancialEnum::QUITADO->value){

                    $financialReleasesService->sendEmailBeneficiary($financial->id_card_pipefy);

                    $financial->update(['status' => StatusFinancialEnum::QUITADO]);

                }

            }

        }

    }
}
