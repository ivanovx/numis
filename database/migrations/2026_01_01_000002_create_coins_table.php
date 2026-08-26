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

            $table->json('title'); // {"bg":"...","en":"...","de":"..."} — Заглавие

            $table->foreignId('series_id')->nullable()
                ->constrained('series')->nullOnDelete();

            // Not translated: numismatic specs written the same way in every language.
            $table->unsignedSmallInteger('year')->nullable()->index();       // Година
            $table->date('issue_date')->nullable();                          // Дата на въвеждане
            $table->string('denomination')->nullable();                     // Номинална стойност
            $table->string('metal')->nullable()->index();                   // Метал, проба
            $table->string('quality')->nullable();                          // Качество
            $table->string('weight')->nullable();                           // Тегло
            $table->string('diameter')->nullable()->index();                // Диаметър (mm)
            $table->string('mintage')->nullable();                          // Тираж

            // Translated: descriptive / place text that can differ by language.
            $table->json('edge')->nullable();                               // Гурт
            $table->json('mint')->nullable();                               // Отсечена в

            $table->string('front_image')->nullable();
            $table->json('front_description')->nullable();
            $table->string('back_image')->nullable();
            $table->json('back_description')->nullable();
            $table->json('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
