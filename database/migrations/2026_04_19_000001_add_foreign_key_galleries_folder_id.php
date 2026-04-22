<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan foreign key constraint untuk galleries.folder_id -> folders.id
     * Menggunakan cascadeOnDelete agar file dalam folder juga dihapus saat folder dihapus.
     */
    public function up(): void
    {
        // Cek apakah kolom folder_id sudah ada
        $columnExists = \DB::getSchemaBuilder()->hasColumn('galleries', 'folder_id');
        
        if (!$columnExists) {
            // Kolom belum ada, tambahkan kolom + foreign key
            Schema::table('galleries', function (Blueprint $table) {
                $table->foreignId('folder_id')
                      ->nullable()
                      ->constrained('folders')
                      ->onDelete('cascade');
            });
        } else {
            // Kolom sudah ada, tambahkan foreign key constraint saja dengan raw SQL
            \DB::statement('
                ALTER TABLE galleries 
                ADD CONSTRAINT galleries_folder_id_foreign 
                FOREIGN KEY (folder_id) 
                REFERENCES folders(id) 
                ON DELETE CASCADE
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
        });
    }
};