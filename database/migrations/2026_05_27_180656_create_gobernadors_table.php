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
        Schema::create('gobernadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titulo_id')->constrained('titulos')->onDelete('cascade');
            $table->string('nombres');
            $table->string('apellidos');
            $table->boolean('estado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gobernadores');
    }
};
