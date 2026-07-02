<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add proper foreign key constraint for shop owner
        Schema::table('shops', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });

        // Add index for shop-staff relationships (check if exists first)
        if (!Schema::hasIndex('users', 'users_shop_id_role_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['shop_id', 'role']);
            });
        }

        // Fix orders table - change nullable shop_id to required with proper cascading
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['shop_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Make shop_id NOT NULL (orders must belong to a shop)
            $table->foreignId('shop_id')->nullable(false)->change();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        // Note: SQLite doesn't support CHECK constraints with subqueries in the same way as other databases.
        // We'll implement this validation at the application level instead.
        
        // Add constraint documentation for future reference
        DB::statement("
            -- This constraint would ensure staff can only be assigned to orders from their own shop:
            -- CHECK (staff_id IS NULL OR (SELECT shop_id FROM users WHERE id = staff_id AND role = 'tailor_staff') = shop_id)
            -- Implemented at application level due to SQLite limitations
        ");

        // Fix garment_types table
        Schema::table('garment_types', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
        });

        Schema::table('garment_types', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable(false)->change();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        // Fix fabrics table
        Schema::table('fabrics', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
        });

        Schema::table('fabrics', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable(false)->change();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        // Fix appointments table - ensure appointments belong to shops
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable(false)->change();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        // Add constraint documentation for appointments
        DB::statement("
            -- This constraint would ensure appointment staff matches shop:
            -- CHECK (staff_id IS NULL OR (SELECT shop_id FROM users WHERE id = staff_id AND role = 'tailor_staff') = shop_id)
            -- Implemented at application level due to SQLite limitations
        ");
    }

    public function down(): void
    {
        // Remove constraint documentation (no actual constraints to remove in SQLite)
        
        // Reverse changes to tables
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'role']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable()->change();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });

        Schema::table('garment_types', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable()->change();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });

        Schema::table('fabrics', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable()->change();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable()->change();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });
    }
};