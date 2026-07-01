<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tracking_number')->unique();
            $table->foreignId('garment_type_id')->nullable()->constrained()->onDelete('set null');
            $table->string('fabric_preference')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('special_instructions')->nullable();
            $table->string('design_reference_path')->nullable();
            $table->enum('status', [
                'pending', 'measurements_verified', 'in_production',
                'fitting_scheduled', 'final_adjustment', 'ready_for_pickup',
                'completed', 'released'
            ])->default('pending');
            $table->date('estimated_completion')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
