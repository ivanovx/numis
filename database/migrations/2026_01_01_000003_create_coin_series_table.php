<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
            $table->unique(['coin_id', 'series_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_series');
    }
};
