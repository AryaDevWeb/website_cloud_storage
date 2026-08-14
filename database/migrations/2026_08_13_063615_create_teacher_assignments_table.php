<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('academic_year');
            $table->enum('role', ['main_teacher', 'assistant_teacher'])->default('main_teacher');
            $table->boolean('is_homeroom_teacher')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'subject_id', 'classroom_id', 'academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_assignments');
    }
};