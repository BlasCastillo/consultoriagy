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
        Schema::table('poas', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('actividad_poas', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('meta_trimestrals', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('replanificacions', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('ficha_actividads', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('imagen_fichas', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('evidencias', function (Blueprint $table) { $table->softDeletes(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poas', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('actividad_poas', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('meta_trimestrals', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('replanificacions', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('ficha_actividads', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('imagen_fichas', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('evidencias', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
