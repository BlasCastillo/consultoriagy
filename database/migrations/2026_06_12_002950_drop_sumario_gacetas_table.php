<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sumarios_gacetas');
    }

    public function down(): void
    {
        // Not necessary for a cleanup migration
    }
};
