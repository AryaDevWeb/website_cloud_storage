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
            $table->string('status')->default('ready')->after('path');
            $table->string('preview_type')->nullable()->after('status');
            $table->text('preview_path')->nullable()->after('preview_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['status', 'preview_type', 'preview_path']);
        });
    }
};
