<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_trimestrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_poa_id')->constrained('actividad_poas')->onDelete('cascade');
            $table->integer('trimestre'); // 1 a 4
            $table->integer('meta_inicial')->default(0);
            $table->integer('meta_actual')->default(0);
            $table->integer('ejecutada')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_trimestrals');
    }
};
