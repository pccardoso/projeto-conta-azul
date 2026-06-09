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
        Schema::table('financial_releases', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->nullable()->after('protocol')->comment('Valor pago');
            $table->text('observation')->nullable()->after('protocol')->comment('Observação');
            $table->text('notes')->nullable()->after('protocol')->comment('Notas');
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::table('financial_releases', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
            $table->dropColumn('observation');
            $table->dropColumn('notes');
        });
    }
};
