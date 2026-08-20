<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $rpl = Major::updateOrCreate(['code' => 'RPL'], [
            'name' => 'Rekayasa Perangkat Lunak', 'short_name' => 'RPL',
            'description' => 'Pengembangan perangkat lunak dan aplikasi.', 'is_active' => true,
        ]);
        $tkj = Major::updateOrCreate(['code' => 'TKJ'], [
            'name' => 'Teknik Komputer dan Jaringan', 'short_name' => 'TKJ',
            'description' => 'Infrastruktur komputer dan jaringan.', 'is_active' => true,
        ]);

        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'type' => 'core', 'major_id' => null, 'hours_per_week' => 4],
            ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek', 'type' => 'specialization', 'major_id' => $rpl->id, 'hours_per_week' => 6],
            ['code' => 'JAR', 'name' => 'Administrasi Infrastruktur Jaringan', 'type' => 'specialization', 'major_id' => $tkj->id, 'hours_per_week' => 6],
        ];
        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['code' => $subject['code']], $subject + ['is_active' => true]);
        }

        $guru = User::where('email', 'guru@smkn1lumajang.sch.id')->first();
        $siswa = User::where('email', 'siswa@smkn1lumajang.sch.id')->first();
        $year = date('Y').'/'.(date('Y') + 1);

        $classroom = Classroom::updateOrCreate(['code' => 'X-RPL-1'], [
            'name' => 'X RPL 1', 'major_id' => $rpl->id, 'grade_level' => 10,
            'academic_year' => $year, 'capacity' => 36,
            'homeroom_teacher_id' => $guru?->id, 'is_active' => true,
        ]);
        Classroom::updateOrCreate(['code' => 'XI-TKJ-1'], [
            'name' => 'XI TKJ 1', 'major_id' => $tkj->id, 'grade_level' => 11,
            'academic_year' => $year, 'capacity' => 36,
            'homeroom_teacher_id' => $guru?->id, 'is_active' => true,
        ]);

        if ($guru) {
            TeacherAssignment::updateOrCreate([
                'user_id' => $guru->id, 'subject_id' => Subject::where('code', 'PBO')->value('id'),
                'classroom_id' => $classroom->id, 'academic_year' => $year,
            ], ['role' => 'main_teacher', 'is_homeroom_teacher' => true, 'is_active' => true]);
        }

        if ($siswa) {
            StudentRecord::updateOrCreate(['student_id' => '0012345678'], [
                'user_id' => $siswa->id, 'classroom_id' => $classroom->id,
                'academic_year' => $year, 'status' => 'active', 'enrollment_date' => now()->toDateString(),
            ]);
        }
    }
}
