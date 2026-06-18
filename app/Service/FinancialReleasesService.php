<?php

    namespace App\Service;

    use App\Models\FinancialReleases;
    use App\Service\ContaAzulService;
    use Illuminate\Support\Facades\Mail;
    use App\Mail\SendEmailOficina;
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

                $responseCardFinancial = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $idCardFinancial);

                if($responseCardFinancial->status() === 200){
                
                    $dataCardFinancial = $responseCardFinancial->json();

                    $listFieldCard = data_get($dataCardFinancial, 'fields', []);

                    $value = collect($listFieldCard)->firstWhere('name', 'Tipo Beneficiário')['value'] ?? null;

                    $positionBeneficiary = match ($value) {
                        "Oficina" => 0,
                        "Fornecedor" => 1,
                    };

                    $idCardBeneficiary = data_get($dataCardFinancial, ('child_relations.'.$positionBeneficiary.'.cards.0.id'), false);


                    if($idCardBeneficiary){
                        
                        $responseCardBeneficiary = Http::withHeaders([
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json'
                        ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $idCardBeneficiary);

                        if($responseCardBeneficiary->status() === 200){

                            $dataCardBeneficiary = $responseCardBeneficiary->json();

                            $dataBankDetails = [];

                            $positionBank = data_get($dataCardBeneficiary, 'child_relations.0.cards.0.id', false);

                            //Buscar informações de pagamento
                            if($positionBank){

                                $responseBankDetails = Http::withHeaders([
                                    'Content-Type' => 'application/json',
                                    'Accept' => 'application/json'
                                ])->get('https://integration-pipefy.mundoevogard.com/pipefy/card/' . $positionBank);

                                if($responseBankDetails->status() === 200){
                                    $dataBankDetails = $responseBankDetails->json();
                                }

                            }
                            
                            $dataFields = array_column($dataCardBeneficiary['fields'], 'value', 'name');

                            if (!empty($dataBankDetails['fields'])) {

                                $bankFields = array_column(
                                    $dataBankDetails['fields'],
                                    'value',
                                    'name'
                                );

                                $dataFields = array_merge($dataFields, $bankFields);
                            }

                            return $dataFields;

                        }
                    }

                    return [];

                }

            }catch(\Exception $e){
                Log::error($e->getMessage());
                return [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'line' => $e->getLine()
                ];
            }

        }

        public function sendEmailBeneficiary(int $idCardFinancial, FinancialReleases $financialReleases){

            $statusEmail = [
                "logs" => null,
                "id_label" => null
            ];

            try{

                $dataBeneficiary = $this->getArrayBeneficiaryPipefy($idCardFinancial);

                if(!empty($dataBeneficiary)){

                    $email = data_get($dataBeneficiary, 'E-mail', null);

                    Log::info('E-mail do beneficiário: ' . $email);

                    if(empty($email)){

                        data_set($statusEmail, 'logs', "E-mail do beneficiário nulo.");
                        data_set($statusEmail, 'id_label', "317777251");

                    }else{

                        Mail::to('xoxo.sto2024@gmail.com')->queue(new SendEmailOficina([
                            ...$dataBeneficiary,
                            ...$financialReleases->toArray()
                        ]));

                        data_set($statusEmail, 'logs', "E-mail enviado com sucesso.");
                        data_set($statusEmail, 'id_label', "317777253");

                    }
                }else{

                    data_set($statusEmail, 'logs', "As informações do beneficiário não foram encontradas, validar as configurações.");
                    data_set($statusEmail, 'id_label', "317777271");

                }

                $financialReleases->update([
                    'logs' => data_get($statusEmail, 'logs', "Evento de e-mail não identificado."),
                ]);

                $updateCardResponse =$this->pipefyService->updateLabel([
                    "cardId" => $idCardFinancial,
                    "labelIds" => [
                        data_get($statusEmail, 'id_label')
                    ]
                ]);

            }catch(\Exception $e){
                Log::error($e->getMessage());
            }

        }

    }