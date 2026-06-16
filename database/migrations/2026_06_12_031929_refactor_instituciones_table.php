<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (Schema::hasColumn('instituciones', 'rif')) {
                $table->dropColumn('rif');
            }
            if (!Schema::hasColumn('instituciones', 'estatus')) {
                $table->boolean('estatus')->default(true)->after('tipo_institucion_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (!Schema::hasColumn('instituciones', 'rif')) {
                $table->string('rif')->nullable();
            }
            if (Schema::hasColumn('instituciones', 'estatus')) {
                $table->dropColumn('estatus');
            }
        });
    }
};
