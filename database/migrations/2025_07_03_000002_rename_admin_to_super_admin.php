<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Role Clarification Migration
 *
 * Renames the 'admin' role to 'super_admin'.
 * MySQL-compatible: uses MODIFY COLUMN to change the enum.
 * SQLite-compatible: falls back to data-only update (SQLite ignores enum constraints).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Step 1: First expand the enum to include BOTH 'admin' and 'super_admin'
            // so existing 'admin' rows are not truncated during the transition
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer','tailor_staff','admin','shop_owner','super_admin') NOT NULL DEFAULT 'customer'");
        }

        // Step 2: Update existing 'admin' records to 'super_admin'
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Step 3: Now safely remove 'admin' from the enum (no rows have it anymore)
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer','tailor_staff','shop_owner','super_admin') NOT NULL DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        // Revert data
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer','tailor_staff','admin','shop_owner') NOT NULL DEFAULT 'customer'");
        }
    }
};
