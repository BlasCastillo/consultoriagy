<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_actividads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_trimestral_id')->constrained('meta_trimestrals')->onDelete('cascade');
            $table->date('fecha');
            $table->string('titulo_actividad');
            $table->string('desarrollada_por');
            $table->integer('cantidad_beneficiados')->default(0);
            $table->text('instituciones_involucradas')->nullable();
            $table->text('objetivo_logrado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_actividads');
    }
};
