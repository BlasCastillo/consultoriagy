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
        Schema::create('gacetas', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->integer('anio');
            $table->enum('tipo', ['Ordinaria', 'Extraordinaria']);
            $table->string('anio_politico')->nullable();
            $table->string('mes_politico')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_recepcion_fisica')->nullable();
            $table->date('fecha_publicacion')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->enum('estado', ['Reservada', 'Pendiente_Subida', 'Publicada', 'Reimpresa'])->default('Reservada');
            $table->unsignedBigInteger('corregida_de_id')->nullable();

            $table->foreign('corregida_de_id')->references('id')->on('gacetas')->onDelete('set null');
            
            $table->unique(['numero', 'anio', 'tipo'], 'gacetas_num_anio_tipo_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gacetas');
    }
};
