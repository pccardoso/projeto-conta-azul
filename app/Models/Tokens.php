<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tokens extends Model
{
    protected $table = 'tokens';

    protected $fillable = [
        'id_token',
        'access_token',
        'refresh_token',
    ];
}
