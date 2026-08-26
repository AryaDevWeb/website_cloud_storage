<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shared_to_role')->nullable();
            $table->enum('permission', ['view', 'download'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['media_id', 'shared_to_user_id']);
            $table->index(['media_id', 'shared_to_role']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};
