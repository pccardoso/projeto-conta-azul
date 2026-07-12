<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeripheralFinancialReleases extends Model
{

    protected $table = 'peripheral_financial_releases';

    protected $fillable = [
        'id_card_pipefy',
        'txid_efi',
    ];

}
