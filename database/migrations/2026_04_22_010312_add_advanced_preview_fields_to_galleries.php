<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('mime_type')->nullable()->after('file');
            $table->string('extension')->nullable()->after('mime_type');
            $table->text('thumbnail_path')->nullable()->after('preview_path');
            $table->string('conversion_status')->default('pending')->after('status');
            $table->timestamp('preview_ready_at')->nullable()->after('conversion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'extension', 'thumbnail_path', 'conversion_status', 'preview_ready_at']);
        });
    }
};
