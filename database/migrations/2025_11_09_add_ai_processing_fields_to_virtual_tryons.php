<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_tryons', function (Blueprint $table) {
            // Track processing status with more detail
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->after('status');

            // Error message if processing fails
            $table->text('error_message')->nullable()->after('processing_status');

            // Processing start & completion times
            $table->timestamp('processing_started_at')->nullable()->after('error_message');
            $table->timestamp('processing_completed_at')->nullable()->after('processing_started_at');

            // Store base64 preview for quick display
            $table->longText('preview_data')->nullable()->after('processing_completed_at');

            // Store metadata about the try-on (garment details, etc)
            $table->json('metadata')->nullable()->after('preview_data');

            // Index for quick querying of pending/processing jobs
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_tryons', function (Blueprint $table) {
            $table->dropIndex('virtual_tryons_processing_status_index');
            $table->dropColumn([
                'processing_status',
                'error_message',
                'processing_started_at',
                'processing_completed_at',
                'preview_data',
                'metadata',
            ]);
        });
    }
};
