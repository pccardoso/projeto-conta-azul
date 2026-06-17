<?php

namespace App\Support;

class PipefyConfiguration{

    /**
     * Configuração de relacionamentos
     * A ideia é pegar o cartão do financeiro, descobrir o pai através da posição do parent
     * Com o parent pegamos a relação com o beneficiário.
     * Na constante CONFIG_RELATIONS temos a configuração de todos os relacionamentos possíveis.
     * Caso haja alguma novo pipe que precise ser relacionado ao Financeiro é necessário adicionar uma nova configuração.
     */

    const CONFIG_RELATIONS = [
        [
            "pipe_id" => 307203830,
            "position_parent_relation" => 7,
            "position_beneficiary" => 1,
            "position_bank_details" => 0
        ]
    ];


    public static function getRelations(){
        return self::CONFIG_RELATIONS;
    }

}