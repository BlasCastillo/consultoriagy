<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_institucion_id')->nullable()->after('id');
            $table->foreign('tipo_institucion_id')
                  ->references('id')->on('tipo_instituciones')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropForeign(['tipo_institucion_id']);
            $table->dropColumn('tipo_institucion_id');
        });
    }
};
