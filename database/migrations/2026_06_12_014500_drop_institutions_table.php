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
        Schema::dropIfExists('institutions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed since it's a structural destruction of legacy table
    }
};
