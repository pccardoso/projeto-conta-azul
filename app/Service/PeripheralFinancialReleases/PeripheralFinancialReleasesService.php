<?php


    namespace App\Service\PeripheralFinancialReleases;

    use App\Models\PeripheralFinancialReleases;
    use App\Service\PipefyService;

    class PeripheralFinancialReleasesService
    {

        public function __construct(
            protected PipefyService $pipefyService
        ){}
    
        public function createPeripheralFinancialRelease(array $data){

            PeripheralFinancialReleases::where('id_card_pipefy', $data['id_card_pipefy'])->delete();

            return PeripheralFinancialReleases::create([
                ...$data
            ]);

        }

        public function getPeripheralFinancialReleaseByIdCardPipefy(int $id_card_pipefy){
            return PeripheralFinancialReleases::where('id_card_pipefy', $id_card_pipefy)->get();
        }

        public function getPeripheralFinancialReleaseByTxidEfi(string $txid_efi){
            return PeripheralFinancialReleases::where('txid_efi', $txid_efi)->get();
        }

        public function moveToPipefyPaymentPix(array $data): array
        {
            $dataReturn = [];

            foreach($data as $item){
            
                $getPheripheralCurrent = PeripheralFinancialReleases::where('txid_efi', $item['txid'])->first();

                if($getPheripheralCurrent){

                    $responsePipefy = $this->pipefyService->moveCard([
                        "cardId" => $getPheripheralCurrent->id_card_pipefy,
                        "phaseId" => 343120341
                    ]);

                    $dataReturn[] = [
                        "txid" => $item['txid'],
                        "response" => $responsePipefy
                    ];

                }

            }

            return $dataReturn;

        }

    }