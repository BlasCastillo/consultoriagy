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
        Schema::table('jefaturas', function (Blueprint $table) {
            if (!Schema::hasColumn('jefaturas', 'nivel')) {
                $table->integer('nivel')->nullable()->comment('1: Consultor, 2: Direccion, 3-5: Coordinacion, 6: Jefatura, 7+: Personal Base')->after('nombre');
            }
            if (!Schema::hasColumn('jefaturas', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('jefaturas')->onDelete('restrict')->comment('ID de la unidad superior a la que rinde cuentas')->after('nivel');
            }
            if (!Schema::hasColumn('jefaturas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jefaturas', function (Blueprint $table) {
            if (Schema::hasColumn('jefaturas', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('jefaturas', 'nivel')) {
                $table->dropColumn('nivel');
            }
            if (Schema::hasColumn('jefaturas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
