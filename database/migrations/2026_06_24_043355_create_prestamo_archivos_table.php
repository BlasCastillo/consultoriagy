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
        Schema::create('prestamos_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gaceta_id')->constrained('gacetas')->onDelete('cascade');
            $table->string('solicitante_cedula');
            $table->string('solicitante_nombres');
            $table->string('contacto')->nullable();
            $table->text('motivo_uso');
            $table->date('fecha_salida');
            $table->date('fecha_devolucion_estimada');
            $table->date('fecha_devolucion_real')->nullable();
            $table->enum('estado_prestamo', ['Activo', 'Devuelto', 'Vencido'])->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos_archivos');
    }
};
