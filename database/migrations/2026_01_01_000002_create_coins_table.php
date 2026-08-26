<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('denomination')->nullable();
            $table->string('metal')->nullable()->index();
            $table->string('diameter')->nullable()->index(); // mm, kept as string to match old free-text filter values
            $table->string('front_image')->nullable(); // storage path
            $table->string('back_image')->nullable();  // storage path
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
