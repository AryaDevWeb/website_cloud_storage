<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
            $table->integer('grade_level'); // 10, 11, 12
            $table->string('academic_year');
            $table->integer('capacity')->default(36);
            $table->string('room')->nullable();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['code', 'academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};