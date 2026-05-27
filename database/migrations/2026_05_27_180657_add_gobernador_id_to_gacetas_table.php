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
        Schema::table('gacetas', function (Blueprint $table) {
            $table->foreignId('gobernador_id')->nullable()->constrained('gobernadores')->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            $table->dropForeign(['gobernador_id']);
            $table->dropColumn('gobernador_id');
        });
    }
};
