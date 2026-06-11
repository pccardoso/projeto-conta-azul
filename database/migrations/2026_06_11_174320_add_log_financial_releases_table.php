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
            $table->text('logs')->nullable()->after('protocol')->comment('Log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_releases', function (Blueprint $table) {
            $table->dropColumn('logs');
        });
    }
};
