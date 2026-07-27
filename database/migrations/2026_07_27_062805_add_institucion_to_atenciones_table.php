<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('atencions', function (Blueprint $table) {
            // Agregamos la columna institucion justo después del correo
            $table->string('institucion')->nullable()->after('correo');
        });
    }

    public function down()
    {
        Schema::table('atencions', function (Blueprint $table) {
            $table->dropColumn('institucion');
        });
    }
};