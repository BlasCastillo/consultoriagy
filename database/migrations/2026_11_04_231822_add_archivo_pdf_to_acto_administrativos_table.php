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
        Schema::table('acto_administrativos', function (Blueprint $table) {
            $table->string('archivo_pdf')->nullable()->after('sumario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acto_administrativos', function (Blueprint $table) {
            $table->dropColumn('archivo_pdf');
        });
    }
};
