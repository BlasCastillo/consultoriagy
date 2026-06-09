<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poas', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->enum('tipo', ['General', 'Jefatura']);
            $table->foreignId('jefatura_id')->nullable()->constrained('jefaturas')->onDelete('set null');
            $table->enum('estado', ['Borrador', 'Aprobado', 'Ejecucion', 'Cerrado'])->default('Borrador');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poas');
    }
};
