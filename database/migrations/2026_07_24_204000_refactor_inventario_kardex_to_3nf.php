<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('estantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gavetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estante_id')->constrained('estantes')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('inventarios_archivos', function (Blueprint $table) {
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->foreignId('estante_id')->nullable()->constrained('estantes')->nullOnDelete();
            $table->foreignId('gaveta_id')->nullable()->constrained('gavetas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventarios_archivos', function (Blueprint $table) {
            $table->dropForeign(['gaveta_id']);
            $table->dropForeign(['estante_id']);
            $table->dropForeign(['sede_id']);
            $table->dropColumn(['gaveta_id', 'estante_id', 'sede_id']);
        });

        Schema::dropIfExists('gavetas');
        Schema::dropIfExists('estantes');
        Schema::dropIfExists('sedes');
    }
};
