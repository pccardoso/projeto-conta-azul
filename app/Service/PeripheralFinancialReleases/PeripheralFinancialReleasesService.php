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

                    $pagador = $item['gnExtras']['pagador'] ?? [];
                    $cpfCnpjPagador = $pagador['cnpj'] ?? $pagador['cpf'] ?? null;

                    $responsePipefyUpdateCard = $this->pipefyService->updateCard([
                        "cardId" => $getPheripheralCurrent->id_card_pipefy,
                        "fields" => [
                            [
                                "field_id" => "data_hora_de_pagamento",
                                "field_value" => isset($item['horario'])
                                    ? \Carbon\Carbon::parse($item['horario'])->timezone(config('app.timezone'))->format('d/m/Y H:i')
                                    : null
                            ],
                            [
                                "field_id" => "informa_es_do_pagador",
                                "field_value" => $item['infoPagador'] ?? null
                            ],
                            [
                                "field_id" => "identificador_de_transa_o",
                                "field_value" => $item['endToEndId'] ?? null
                            ],
                            [
                                "field_id" => "identificador_de_pagamento_txid",
                                "field_value" => $item['txid']
                            ],
                            [
                                "field_id" => "c_digo_banco",
                                "field_value" => $pagador['codigoBanco'] ?? null
                            ],
                            [
                                "field_id" => "cpf_cnpj_pagador",
                                "field_value" => $cpfCnpjPagador
                            ],
                            [
                                "field_id" => "nome_pagador",
                                "field_value" => $pagador['nome'] ?? null
                            ]
                        ]
                    ]);

                    $responsePipefy = $this->pipefyService->moveCard([
                        "cardId" => $getPheripheralCurrent->id_card_pipefy,
                        "phaseId" => 343120341
                    ]);

                    $dataReturn[] = [
                        "txid" => $item['txid'],
                        "responseMoveCard" => $responsePipefy,
                        "responseUpdateCard" => $responsePipefyUpdateCard
                    ];

                }

            }

            return $dataReturn;

        }

    }