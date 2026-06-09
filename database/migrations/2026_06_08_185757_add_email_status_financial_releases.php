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
            $table->boolean('email_status')->default(false)->after('protocol')->comment('Status do envio de email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_releases', function (Blueprint $table) {
            $table->dropColumn('email_status');
        });
    }
};
