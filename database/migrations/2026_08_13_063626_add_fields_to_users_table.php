<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nisn')->nullable()->unique()->after('email');
            $table->string('nip')->nullable()->unique()->after('nisn');
            $table->string('phone')->nullable()->after('nip');
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->enum('gender', ['L', 'P'])->nullable()->after('birth_date');
            $table->string('profile_photo')->nullable()->after('gender');
            $table->boolean('is_active')->default(true)->after('profile_photo');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'nip', 'phone', 'address', 'birth_date', 'gender', 'profile_photo', 'is_active']);
        });
    }
};