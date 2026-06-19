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
        if (!Schema::hasColumn('solicitudes', 'motivo_rechazo')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->text('motivo_rechazo')->nullable()->comment('Motivo de rechazo del documento');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('solicitudes', 'motivo_rechazo')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->dropColumn('motivo_rechazo');
            });
        }
    }
};
