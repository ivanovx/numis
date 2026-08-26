<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()
                ->constrained('series')->nullOnDelete();
            $table->date('issue_date')->nullable();
            $table->string('quality')->nullable();
            $table->string('weight')->nullable();
            $table->string('mintage')->nullable();
            $table->json('edge')->nullable();
            $table->json('mint')->nullable();
            $table->json('front_description')->nullable();
            $table->json('back_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropColumn([
                'series_id',
                'issue_date',
                'quality',
                'weight',
                'mintage',
                'edge',
                'mint',
                'front_description',
                'back_description',
            ]);
        });
    }
};