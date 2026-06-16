<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes', 'institucion_id')) {
                $table->unsignedBigInteger('institucion_id')->nullable();
                $table->foreign('institucion_id')->references('id')->on('instituciones')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'institucion_id')) {
                $table->dropForeign(['institucion_id']);
                $table->dropColumn('institucion_id');
            }
        });
    }
};
