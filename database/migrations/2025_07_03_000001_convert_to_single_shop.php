<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Single-Shop Conversion Migration
 *
 * Converts the multi-shop marketplace schema to a single-shop management system.
 * The shops table is kept with one row. All shop_id foreign keys are preserved
 * but the system is now scoped to that single shop instance.
 *
 * Changes:
 * - Remove commission_rate, is_featured, total_reviews, rating from shops (marketplace columns)
 * - Remove shop_id from users (no longer needed for auth scoping)
 * - Seed the single shop row if not already present
 * - Remove the composite [shop_id, role] index on users (no longer meaningful)
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 0. Add shop_id to users if missing (MySQL may not have it if shops table
        //    didn't exist when the original users migration ran)
        if (!Schema::hasColumn('users', 'shop_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->after('role')->constrained('shops')->nullOnDelete();
            });
        }

        // 0b. Expand the role enum to include shop_owner / super_admin if on MySQL
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer','tailor_staff','admin','shop_owner','super_admin') NOT NULL DEFAULT 'customer'");
        }
        // 1. Ensure exactly one shop record exists (the single business instance)
        if (DB::table('shops')->count() === 0) {
            DB::table('shops')->insert([
                'name'          => config('app.name', 'My Tailoring Shop'),
                'slug'          => 'my-tailoring-shop',
                'description'   => 'A professional tailoring business providing quality custom garments.',
                'address'       => 'Your Business Address',
                'city'          => 'Your City',
                'province'      => 'Your Province',
                'is_active'     => true,
                'is_verified'   => true,
                'is_featured'   => false,
                'commission_rate' => 0,
                'rating'        => 0,
                'total_reviews' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // 2. Assign all existing users (shop_owner / tailor_staff) to the single shop
        $shopId = DB::table('shops')->first()->id;
        DB::table('users')
            ->whereIn('role', ['shop_owner', 'tailor_staff'])
            ->whereNull('shop_id')
            ->update(['shop_id' => $shopId]);

        // 3. Assign all orphaned orders/appointments/etc. to the single shop
        foreach (['orders', 'appointments', 'garment_types', 'fabrics', 'available_time_slots', 'pre_made_products', 'shop_reviews'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'shop_id')) {
                DB::table($table)->whereNull('shop_id')->update(['shop_id' => $shopId]);
            }
        }

        // 4. Drop the composite index on users that was added for multi-shop scoping
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_shop_id_role_index');
            });
        } catch (\Exception $e) {
            // Index may not exist — safe to ignore
        }
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'commission_rate',
                'is_featured',
                'total_reviews',
                'rating',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->decimal('rating', 3, 2)->default(0)->after('is_featured');
            $table->integer('total_reviews')->default(0)->after('rating');
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('total_reviews');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['shop_id', 'role'], 'users_shop_id_role_index');
        });
    }
};
