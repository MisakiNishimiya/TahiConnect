<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add proper foreign key constraint for shop owner (skip if already exists)
        try {
            Schema::table('shops', function (Blueprint $table) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Exception $e) {
            // FK may already exist — safe to continue
        }

        // Add index for shop-staff relationships (check if exists first)
        if (!Schema::hasIndex('users', 'users_shop_id_role_index')) {
            if (Schema::hasColumn('users', 'shop_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index(['shop_id', 'role']);
                });
            }
        }

        // Fix orders table - drop existing FK then re-add with cascade
        // Note: NOT NULL enforcement is handled by convert_to_single_shop after data is seeded
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['shop_id']);
            });
        } catch (\Exception $e) { /* FK may not exist */ }

        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            });
        } catch (\Exception $e) { /* FK may already exist */ }

        // Fix garment_types table
        try {
            Schema::table('garment_types', function (Blueprint $table) {
                $table->dropForeign(['shop_id']);
            });
        } catch (\Exception $e) { /* FK may not exist */ }

        try {
            Schema::table('garment_types', function (Blueprint $table) {
                $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            });
        } catch (\Exception $e) { /* FK may already exist */ }

        // Fix fabrics table
        try {
            Schema::table('fabrics', function (Blueprint $table) {
                $table->dropForeign(['shop_id']);
            });
        } catch (\Exception $e) { /* FK may not exist */ }

        try {
            Schema::table('fabrics', function (Blueprint $table) {
                $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            });
        } catch (\Exception $e) { /* FK may already exist */ }

        // Fix appointments table
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['shop_id']);
            });
        } catch (\Exception $e) { /* FK may not exist */ }

        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            });
        } catch (\Exception $e) { /* FK may already exist */ }
    }

    public function down(): void
    {
        try {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['shop_id', 'role']);
            });
        } catch (\Exception $e) {}

        foreach (['orders', 'garment_types', 'fabrics', 'appointments'] as $tbl) {
            try {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropForeign(['shop_id']);
                });
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $table->foreignId('shop_id')->nullable()->change();
                    $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
                });
            } catch (\Exception $e) {}
        }
    }
};