<?php

    namespace App\Service;

    use App\Models\FinancialReleases;
    use App\Service\ContaAzulService;

    class FinancialReleasesService
    {

        public function __construct(
            protected ContaAzulService $contaAzulService
        ){}
        
        public function createFinancialRelease(array $data){

            $getEventId = $this->contaAzulService->getProtocol($data['protocol']);
            
            data_set($data, 'event', $getEventId['evento_financeiro_id'] ?? null);

            return FinancialReleases::create($data);

        }

    }