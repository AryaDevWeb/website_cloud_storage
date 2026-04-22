<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan foreign key constraint untuk folders.parent_id -> folders.id
     * Menggunakan nullOnDelete agar subfolder menjadi root folder saat parent dihapus.
     */
    public function up(): void
    {
        // Cek apakah constraint sudah ada
        $constraintExists = \DB::select("
            SELECT 1 FROM information_schema.table_constraints 
            WHERE constraint_name = 'folders_parent_id_foreign' 
            AND table_name = 'folders'
        ");

        if (empty($constraintExists)) {
            // Cek apakah kolom parent_id sudah ada
            $columnExists = \DB::getSchemaBuilder()->hasColumn('folders', 'parent_id');
            
            if (!$columnExists) {
                // Kolom belum ada, tambahkan kolom + foreign key
                Schema::table('folders', function (Blueprint $table) {
                    $table->foreignId('parent_id')
                          ->nullable()
                          ->constrained('folders')
                          ->onDelete('set null');
                });
            } else {
                // Kolom sudah ada, hanya tambahkan foreign key constraint saja
                \DB::statement('
                    ALTER TABLE folders 
                    ADD CONSTRAINT folders_parent_id_foreign 
                    FOREIGN KEY (parent_id) 
                    REFERENCES folders(id) 
                    ON DELETE SET NULL
                ');
            }
        }
        // Jika constraint sudah ada, tidak perlu melakukan apa-apa
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
    }
};