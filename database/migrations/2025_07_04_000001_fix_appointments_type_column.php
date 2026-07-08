<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix appointments.type column on MySQL.
 *
 * The MySQL database had an older migration that created appointments.type
 * as an enum('fitting','consultation','pickup'). The current migration
 * defines it as a plain string to support all appointment types.
 * This migration converts the column to varchar(255).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Convert enum to string, preserving existing values
            DB::statement("ALTER TABLE appointments MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'consultation'");
        }

        // Ensure any legacy type values are mapped to valid new values
        DB::table('appointments')
            ->where('type', 'pickup')
            ->update(['type' => 'final_pickup']);
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN type ENUM('fitting','consultation','pickup') NOT NULL DEFAULT 'consultation'");
        }
    }
};
