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
        Schema::create('inventarios_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gaceta_id')->constrained('gacetas')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios_archivos');
    }
};
