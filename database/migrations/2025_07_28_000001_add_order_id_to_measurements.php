<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add order_id to measurements so a measurement record can be
 * associated with a specific order (order-specific flow) rather than
 * only being a reusable customer profile.
 *
 * Existing records keep order_id = null (legacy profile-style records).
 * New records created during the pre-made ordering flow will have an order_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            // Nullable so existing records are preserved without change
            $table->foreignId('order_id')
                ->nullable()
                ->after('user_id')
                ->constrained('orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
