<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('files') ? 'files' : 'galleries';

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'izin')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('izin')->default(false)->change();
            });
        }
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('files') ? 'files' : 'galleries';

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'izin')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('izin')->default(true)->change();
            });
        }
    }
};
