<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('academic_year');
            $table->string('student_id')->unique();
            $table->enum('status', ['active', 'inactive', 'graduate', 'dropout'])->default('active');
            $table->date('enrollment_date');
            $table->timestamps();
            
            $table->unique(['user_id', 'academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_records');
    }
};