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
    ];

    protected $casts = [
        'status' => StatusFinancialEnum::class,
        'id_card_pipefy' => 'integer'
    ];

}
