<?php


    namespace App\Service\PeripheralFinancialReleases;

    use App\Models\PeripheralFinancialReleases;

    class PeripheralFinancialReleasesService
    {
    
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



    }