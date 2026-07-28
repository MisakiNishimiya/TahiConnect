<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add measurement_standards and measurement_tolerances JSON columns to
 * pre_made_products so the Tolerance-Based Measurement Validation Algorithm
 * can retrieve garment standards (GM) and tolerance values (T) per product.
 *
 * measurement_standards: { "chest": 96, "waist": 80, ... }  — GM values in cm
 * measurement_tolerances: { "chest": 5, "waist": 4, ... }   — T values in cm
 *
 * Both are nullable so existing products are not broken.
 * Products without standards will skip compatibility validation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_made_products', function (Blueprint $table) {
            $table->json('measurement_standards')->nullable()->after('available_sizes');
            $table->json('measurement_tolerances')->nullable()->after('measurement_standards');
        });
    }

    public function down(): void
    {
        Schema::table('pre_made_products', function (Blueprint $table) {
            $table->dropColumn(['measurement_standards', 'measurement_tolerances']);
        });
    }
};
