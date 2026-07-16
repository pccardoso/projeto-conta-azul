<?php

    namespace App\Service;

    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class PipefyService{

        private const PIPE_ID_PERIFERICO = 306857755;

        public function getAccessToken(): ?string {

            $cachedToken = Cache::get('pipefy_access_token');

            if ($cachedToken) {
                return $cachedToken;
            }

            $response = Http::asJson()
                ->acceptJson()
                ->post(config('services.pipefy.token_url'), [
                    'client_id' => config('services.pipefy.client_id'),
                    'client_secret' => config('services.pipefy.client_secret'),
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->status() !== 200) {
                Log::error('Erro ao autenticar na API do Pipefy: ' . $response->body());
                return null;
            }

            $accessToken = $response->json('access_token');

            Cache::put('pipefy_access_token', $accessToken, now()->addDays(29));

            return $accessToken;

        }

        public function updateCard(array $data) {

            Log::info('Dados para atualização do card: '.json_encode($data));

            $query = <<<'GRAPHQL'
                mutation UpdateFieldsValues($input: UpdateFieldsValuesInput!) {
                    updateFieldsValues(input: $input) {
                        success
                    }
                }
                GRAPHQL;

            $values = array_map(fn($field) => [
                'fieldId' => $field['field_id'],
                'value' => $field['field_value'],
            ], $data['fields']);

            $responseUpdate = Http::withToken($this->getAccessToken())
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => [
                        'input' => [
                            'nodeId' => (string) $data['cardId'],
                            'values' => $values,
                        ],
                    ],
                ]);

            Log::info('Resposta da API de atualização do card: '.json_encode($responseUpdate->body()));

            if($responseUpdate->status() === 200 && empty($responseUpdate->json('errors'))){
                return $responseUpdate->json('data');
            }

            return false;

        }

        public function moveCard(array $data) {

            $query = <<<'GRAPHQL'
                mutation MoveCardToPhase($input: MoveCardToPhaseInput!) {
                    moveCardToPhase(input: $input) {
                        card {
                            id
                            title
                            current_phase {
                                id
                                name
                            }
                        }
                    }
                }
                GRAPHQL;

            $responseMove = Http::withToken($this->getAccessToken())
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => [
                        'input' => [
                            'card_id' => (string) $data['cardId'],
                            'destination_phase_id' => (string) $data['phaseId'],
                        ],
                    ],
                ]);

            Log::info('Resposta da API de atualização do card: '.json_encode($responseMove->body()));

            if($responseMove->status() === 200 && empty($responseMove->json('errors'))){
                return $responseMove->json('data.moveCardToPhase.card');
            }

            return false;

        }

        public function updateLabel(array $data){

            //A API do Pipefy substitui todos os labels do card, então mesclamos com os labels atuais
            //para preservar o comportamento do serviço anterior (adiciona, não substitui)
            $currentCard = $this->getCard($data['cardId']);

            $currentLabelIds = collect(data_get($currentCard, 'labels', []))->pluck('id')->all();

            $mergedLabelIds = array_values(array_unique([...$currentLabelIds, ...$data['labelIds']]));

            $query = <<<'GRAPHQL'
                mutation UpdateCard($input: UpdateCardInput!) {
                    updateCard(input: $input) {
                        card {
                            id
                            title
                            labels {
                                id
                                name
                            }
                        }
                    }
                }
                GRAPHQL;

            $responseUpdateLabel = Http::withToken($this->getAccessToken())
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => [
                        'input' => [
                            'id' => (string) $data['cardId'],
                            'label_ids' => $mergedLabelIds,
                        ],
                    ],
                ]);

            if($responseUpdateLabel->status() === 200 && empty($responseUpdateLabel->json('errors'))){
                return $responseUpdateLabel->json('data.updateCard.card');
            }

            return false;

        }


        public function createCard(array $data) {

            Log::info('Dados para criação do card: '.json_encode($data));

            $query = <<<'GRAPHQL'
                mutation CreateCard($input: CreateCardInput!) {
                    createCard(input: $input) {
                        card {
                            id
                            title
                        }
                    }
                }
                GRAPHQL;

            $fieldsAttributes = array_map(fn($field) => [
                'field_id' => $field['field_id'],
                'field_value' => $field['field_value'],
            ], $data['fields'] ?? []);

            $input = [
                'pipe_id' => (string) ($data['pipeId'] ?? self::PIPE_ID_PERIFERICO),
                'title' => $data['title'],
                'fields_attributes' => $fieldsAttributes,
            ];

            if (!empty($data['parentIds'])) {
                $input['parent_ids'] = array_map('strval', $data['parentIds']);
            }

            $responseCreate = Http::withToken(config('services.pipefy.personal_access_token'))
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => [
                        'input' => $input,
                    ],
                ]);

            Log::info('Resposta da API de criação do card: '.json_encode($responseCreate->body()));

            if($responseCreate->status() === 200 && empty($responseCreate->json('errors'))){
                return $responseCreate->json('data.createCard.card');
            }

            return false;

        }

        public function getCard(int $idCard){

            $query = <<<'GRAPHQL'
                query GetCard($id: ID!) {
                    card(id: $id) {
                        id
                        title
                        created_at: createdAt
                        updated_at
                        pipe {
                            id
                            name
                        }
                        current_phase {
                            id
                            name
                        }
                        labels {
                            id
                            name
                        }
                        fields {
                            name
                            value
                            field {
                                id
                            }
                        }
                        parent_relations {
                            cards {
                                id
                                title
                            }
                        }
                        child_relations {
                            cards {
                                id
                                title
                                current_phase {
                                    name
                                }
                            }
                        }
                    }
                }
                GRAPHQL;

            $responseGetCard = Http::withToken($this->getAccessToken())
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => ['id' => (string) $idCard],
                ]);

            if($responseGetCard->status() === 200 && empty($responseGetCard->json('errors'))){
                return $responseGetCard->json('data.card');
            }

            Log::error('Erro ao consultar card no Pipefy: ' . $responseGetCard->body());

            return false;
        }

        public function getCardWithRelations(int $idCard): array {

            $query = <<<'GRAPHQL'
                query GetCardWithRelations($id: ID!) {
                    card(id: $id) {
                        id
                        fields {
                            name
                            value
                        }
                        child_relations {
                            cards {
                                id
                                fields {
                                    name
                                    value
                                }
                                child_relations {
                                    cards {
                                        id
                                        fields {
                                            name
                                            value
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                GRAPHQL;

            $response = Http::withToken($this->getAccessToken())
                ->post('https://api.pipefy.com/graphql', [
                    'query' => $query,
                    'variables' => ['id' => (string) $idCard],
                ]);

            if($response->status() !== 200 || !empty($response->json('errors'))){
                Log::error('Erro ao consultar card no Pipefy: ' . $response->body());
                return [];
            }

            return $response->json('data.card') ?? [];

        }
    }