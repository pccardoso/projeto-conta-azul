<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enum\StatusFinancialEnum;

class FinancialReleases extends Model
{

    protected $table = "financial_releases";

    protected $fillable = [
        'id_card_pipefy',
        'status',
        'protocol',
        'event',
        'type_event',
        'date_of_competence',
        'valor',
        'valor_bruto',
        'due_date',
        'due_date_expected',
        'amount_paid',
        'observation',
        'notes',
        'email_status',
        'logs',
        'payment_date'
    ];

    protected $casts = [
        'status' => StatusFinancialEnum::class,
        'id_card_pipefy' => 'integer',
        'email_status' => 'boolean'
    ];

}
