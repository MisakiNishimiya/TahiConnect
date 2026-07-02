<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', ['custom', 'pre_made'])->default('custom')->after('staff_id');
            $table->foreignId('pre_made_product_id')->nullable()->after('garment_type_id')->constrained('pre_made_products')->nullOnDelete();
            $table->string('product_size')->nullable()->after('pre_made_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pre_made_product_id']);
            $table->dropColumn(['order_type', 'pre_made_product_id', 'product_size']);
        });
    }
};
