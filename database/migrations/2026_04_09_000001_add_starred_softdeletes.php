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
        });

        Schema::table('folders', function (Blueprint $table) {
          
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('starred');
        });
        Schema::table('folders', function (Blueprint $table) {
         
        });
    }
};
