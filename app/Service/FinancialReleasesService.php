<?php

    namespace App\Service;

    use App\Models\FinancialReleases;

    class FinancialReleasesService
    {
        
        public function createFinancialRelease(array $data){
            return FinancialReleases::create($data);
        }

    }