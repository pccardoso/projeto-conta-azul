<?php

    namespace App\Service;

    use App\Models\FinancialReleases;
    use App\Service\ContaAzulService;
    use Illuminate\Support\Facades\Mail;
    use App\Mail\SendEmailOficina;
    use App\Support\PipefyConfiguration;
    use Symfony\Component\HttpKernel\Exception\HttpException;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use App\Service\PipefyService;

    class FinancialReleasesService
    {

        public function __construct(
            protected ContaAzulService $contaAzulService,
            protected PipefyService $pipefyService
        ){}
        
        public function createFinancialRelease(array $data){

            $getEventId = $this->contaAzulService->getProtocol($data['protocol']);
            
            data_set($data, 'event', $getEventId['evento_financeiro_id'] ?? null);

            if($getEventId){

                //Recuperando mais dados do Evento
                $dataEvent = $this->contaAzulService->getEvent($getEventId['evento_financeiro_id']);

                $tipoEvento = data_get($dataEvent, '0.evento.tipo', null);
                $dataCompetencia = data_get($dataEvent, '0.evento.data_competencia', null);
                $valor = data_get($dataEvent, '0.evento.rateio.0.valor', null);
                $valorBruto = data_get($dataEvent, '0.evento.rateio.0.valor_bruto', null);
                $dataVencimento = data_get($dataEvent, '0.data_vencimento', null);
                $dataPagamentoPrevisto = data_get($dataEvent, '0.data_pagamento_previsto', null);
                $observation = data_get($dataEvent, '0.descricao', null);
                $notes = data_get($dataEvent, '0.nota', null);

                return FinancialReleases::create([
                    ...$data,
                    'type_event' => $tipoEvento,
                    'date_of_competence' => $dataCompetencia,
                    'valor' => $valor,
                    'valor_bruto' => $valorBruto,
                    'due_date' => $dataVencimento,
                    'due_date_expected' => $dataPagamentoPrevisto,
                    //'amount_paid' => $amountPaid,
                    'observation' => $observation,
                    'notes' => $notes
                ]);

            }

        }

        public function getArrayBeneficiaryPipefy (int $idCardFinancial): array{

            try{

                $configPipeRelations = PipefyConfiguration::getRelations();

                if(empty($configPipeRelations)){
                    throw new HttpException(400, 'Nenhuma configuração de relacionamento foi encontrada.');
                }

                $responseCardFinancial = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $idCardFinancial);

                if($responseCardFinancial->status() === 200){
                
                    $dataCardFinancial = $responseCardFinancial->json();

                    foreach ($configPipeRelations as $key => $configCurrent) {
                    
                        $idCardParent = data_get($dataCardFinancial, ('parent_relations.' . $configCurrent["position_parent_relation"] . '.cards.0.id'), false);

                        if($idCardParent){
                            $responseCardParent = Http::withHeaders([
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json'
                            ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $idCardParent);

                            if($responseCardParent->status() === 200){
                            
                                $dataCardParent = $responseCardParent->json();

                                $idCardBeneficiary = data_get($dataCardParent, ('child_relations.' . $configCurrent["position_beneficiary"] . '.cards.0.id'), false);

                                if($idCardBeneficiary){
                                    $responseCardBeneficiary = Http::withHeaders([
                                        'Content-Type' => 'application/json',
                                        'Accept' => 'application/json'
                                    ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $idCardBeneficiary);

                                    if($responseCardBeneficiary->status() === 200){
                                        $dataCardBeneficiary = $responseCardBeneficiary->json();
                                        
                                        $dataFields = array_column($dataCardBeneficiary['fields'], 'value', 'name');

                                        return $dataFields;

                                    }
                                }

                                continue;

                            }
                        }

                        continue;

                    }

                    Log::error('Nenhuma configuração de relacionamento foi encontrada para o card: '.$idCardFinancial);

                    return [];

                }

            }catch(\Exception $e){
                throw new \Exception($e->getMessage());
            }

        }

        public function sendEmailBeneficiary(int $idCardFinancial, FinancialReleases $financialReleases){

            $statusEmail = false;

            try{

                $dataBeneficiary = $this->getArrayBeneficiaryPipefy($idCardFinancial);

                if(!empty($dataBeneficiary)){

                    $email = data_get($dataBeneficiary, 'E-mail', null);

                    Log::info('E-mail do beneficiário: ' . $email);

                    if(empty($email)){

                        $financialReleases->update([
                            'logs' => "E-mail do beneficiário nulo."
                        ]);

                        $statusEmail = "E-mail do beneficiário nulo.";

                    }else{

                        Mail::to('xoxo.sto2024@gmail.com')->queue(new SendEmailOficina([
                            ...$dataBeneficiary,
                            ...$financialReleases->toArray()
                        ]));

                        $financialReleases->update([
                            'email_status' => true,
                            'logs' => "E-mail enviado com sucesso."
                        ]);

                        $statusEmail = "E-mail enviado com sucesso.";

                    }
                }else{

                    Log::info('As informações do beneficiário não foram encontradas, validar as configurações.');
                    $financialReleases->update([
                        'logs' => "As informações do beneficiário não foram encontradas, validar as configurações."
                    ]);

                    $statusEmail = "As informações do beneficiário não foram encontradas, validar as configurações.";

                }

                $moveCardResponse = $this->pipefyService->moveCard([
                    "cardId" => $idCardFinancial,
                    "phaseId" => 343385761
                ]);

                $updateCardResponse =$this->pipefyService->updateCard([
                    "cardId" => $idCardFinancial,
                    "fields" => [
                        [
                            "field_id" => "e_mail_enviado",
                            "field_value" => $statusEmail
                        ]
                    ]
                ]);

            }catch(\Exception $e){
                Log::error($e->getMessage());
            }

        }

    }