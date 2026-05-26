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
        Schema::create('sumarios_gacetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaceta_id');
            $table->unsignedBigInteger('institucion_id');
            $table->enum('tipo_acto', ['Ley', 'Decreto', 'Resolución', 'Acuerdo', 'Providencia', 'Aviso Oficial']);
            $table->string('numero_acto');
            $table->text('descripcion');

            $table->foreign('gaceta_id')->references('id')->on('gacetas')->onDelete('cascade');
            $table->foreign('institucion_id')->references('id')->on('institutions')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sumarios_gacetas');
    }
};
