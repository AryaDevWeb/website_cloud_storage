<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->boolean('starred')->default(false);
            $table->softDeletes();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('starred');
            $table->dropSoftDeletes();
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
