<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peripheral_financial_releases', function (Blueprint $table) {
            $table->id();
            $table->integer('id_card_pipefy')->nullable()->comment('ID do card no Pipefy');
            $table->string('txid_efi')->nullable()->comment('TXID do periferico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peripheral_financial_releases');
    }
};
