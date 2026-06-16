<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes', 'gaceta_id')) {
                $table->unsignedBigInteger('gaceta_id')->nullable()->after('estado');
                $table->foreign('gaceta_id')->references('id')->on('gacetas')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['gaceta_id']);
            $table->dropColumn('gaceta_id');
        });
    }
};
