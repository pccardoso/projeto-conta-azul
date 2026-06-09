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
            $table->string('type_event')->nullable()->after('protocol')->comment('Tipo do Evento (Despesa/Receita)');
            $table->string('date_of_competence')->nullable()->after('protocol')->comment('Data da Competência');
            $table->decimal('valor', 10, 2)->nullable()->after('protocol')->comment('Valor'); 
            $table->decimal('valor_bruto', 10, 2)->nullable()->after('protocol')->comment('Valor bruto');  
            $table->string('due_date')->nullable()->after('protocol')->comment('Data de Vencimento');
            $table->string('due_date_expected')->nullable()->after('protocol')->comment('Data de Vencimento Esperado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_releases', function (Blueprint $table) {
            $table->dropColumn('type_event');
            $table->dropColumn('date_of_competence');
            $table->dropColumn('valor');
            $table->dropColumn('valor_bruto');
            $table->dropColumn('due_date');
            $table->dropColumn('due_date_expected');
        });
    }
};
