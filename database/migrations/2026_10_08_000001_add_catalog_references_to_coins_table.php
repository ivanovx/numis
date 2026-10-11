<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            // Not unique on purpose: one Krause number often covers several
            // dates/varieties, and the same BNB emission can span denominations.
            $table->string('km_number', 64)->nullable()->index();       // Krause-Mishler "KM#"
            $table->string('bnb_number', 100)->nullable()->index();     // BNB emission code, e.g. NC_2025_9_100LV
            $table->string('numista_number', 64)->nullable()->index();  // Numista catalogue number
            $table->string('other_references')->nullable();             // Free text: Schön, Фриш, etc.
        });
    }

    public function down(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->dropIndex(['km_number']);
            $table->dropIndex(['bnb_number']);
            $table->dropIndex(['numista_number']);

            $table->dropColumn(['km_number', 'bnb_number', 'numista_number', 'other_references']);
        });
    }
};
