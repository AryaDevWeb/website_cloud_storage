<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Gallery;
use App\Models\User;
use App\Services\FileArchiveService;
use App\Services\RbacScopeService;
use Database\Seeders\SharedDriveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SMKStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_registration_and_login_endpoints_are_enabled(): void
    {
        $this->get('/register')->assertOk();
        $this->get('/login')->assertOk();
        
        // Assert validations are in place (resulting in 302 for HTML, 422 for JSON)
        $this->post('/register')->assertStatus(302);
        $this->post('/login')->assertStatus(302);
        
        $this->postJson('/api/v1/auth/register')->assertStatus(422);
        $this->postJson('/api/v1/auth/login')->assertStatus(422);
    }

    public function test_upload_blocks_forbidden_and_large_video_files(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/upload', [
                'file' => UploadedFile::fake()->create('payload.exe', 1, 'application/x-msdownload'),
            ])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->postJson('/api/upload', [
                'file' => UploadedFile::fake()->create('installer.iso', 1, 'application/x-iso9660-image'),
            ])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->postJson('/api/upload', [
                'file' => UploadedFile::fake()->create('lesson.mp4', 51 * 1024, 'video/mp4'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'File MP4 tidak boleh lebih dari 50 MB.');
    }

    public function test_upload_archives_original_file_and_download_recovers_it(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();
        $uploadedFile = UploadedFile::fake()->createWithContent('implementation_plan.md', "# Plan\nOriginal body");

        $response = $this->actingAs($user)->postJson('/api/upload', [
            'file' => $uploadedFile,
        ]);

        $response->assertOk()
            ->assertJsonPath('ext', 'md');

        $gallery = Gallery::findOrFail($response->json('id'));

        Storage::disk('local')->assertExists($gallery->path);
        $this->assertSame('implementation_plan.md', $gallery->nama_tampilan);
        $this->assertSame('md', $gallery->extension);
        $this->assertSame('done', $gallery->conversion_status);
        $this->assertGreaterThan(0, $gallery->compressed_size);

        $extracted = FileArchiveService::extractFirstFileToTemp(Storage::disk('local')->path($gallery->path));
        $this->assertSame("# Plan\nOriginal body", file_get_contents($extracted['path']));
        @unlink($extracted['path']);

        $download = $this->actingAs($user)->get('/download/' . $gallery->id);
        $download->assertOk();

        $this->assertSame("# Plan\nOriginal body", $download->streamedContent());
    }

    public function test_rbac_allows_students_to_write_only_inside_assignment_folders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'target_kelas' => 'XII RPL',
            'target_jurusan' => 'RPL',
        ]);
        $otherStudent = User::factory()->create([
            'role' => 'siswa',
            'target_kelas' => 'XI TKJ',
            'target_jurusan' => 'TKJ',
        ]);

        $sharedDrive = Folder::create([
            'nama_folder' => 'Shared Drive XII RPL',
            'user_id' => $admin->id,
            'is_shared_drive' => true,
            'scope_kelas' => 'XII RPL',
            'permission' => 1,
            'path' => '',
        ]);

        $materials = Folder::create([
            'nama_folder' => 'Materi',
            'user_id' => $admin->id,
            'parent_id' => $sharedDrive->id,
            'permission' => 1,
            'path' => '',
        ]);

        $assignment = Folder::create([
            'nama_folder' => 'Pengumpulan Tugas',
            'user_id' => $admin->id,
            'parent_id' => $sharedDrive->id,
            'is_assignment_folder' => true,
            'permission' => 1,
            'path' => '',
        ]);

        $this->assertTrue(RbacScopeService::canAccessFolder($student, $sharedDrive));
        $this->assertFalse(RbacScopeService::canWriteFolder($student, $sharedDrive));
        $this->assertFalse(RbacScopeService::canWriteFolder($student, $materials));
        $this->assertTrue(RbacScopeService::canWriteFolder($student, $assignment));
        $this->assertFalse(RbacScopeService::canAccessFolder($otherStudent, $sharedDrive));
    }

    public function test_shared_drive_seeder_creates_rpl_drives_for_x_xi_and_xii(): void
    {
        $this->seed(SharedDriveSeeder::class);

        foreach (['X RPL', 'XI RPL', 'XII RPL'] as $className) {
            $drive = Folder::where('nama_folder', "Shared Drive {$className}")
                ->where('is_shared_drive', true)
                ->where('scope_kelas', $className)
                ->first();

            $this->assertNotNull($drive);
            $this->assertDatabaseHas('folders', [
                'nama_folder' => "Materi {$className}",
                'parent_id' => $drive->id,
                'is_assignment_folder' => false,
            ]);
            $this->assertDatabaseHas('folders', [
                'nama_folder' => "Pengumpulan Tugas {$className}",
                'parent_id' => $drive->id,
                'is_assignment_folder' => true,
            ]);
        }

        $this->assertDatabaseHas('folders', [
            'nama_folder' => 'Shared Drive Jurusan RPL',
            'is_shared_drive' => true,
            'scope_jurusan' => 'RPL',
        ]);
        $this->assertDatabaseMissing('folders', ['nama_folder' => 'Shared Drive XI TKJ']);
    }

    public function test_admin_can_update_google_user_role_scope_and_quota(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}", [
            'role' => 'guru_wali',
            'target_kelas' => 'XI RPL',
            'target_jurusan' => 'RPL',
            'storage_limit_gb' => 20,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'guru_wali',
            'target_kelas' => 'XI RPL',
            'target_jurusan' => 'RPL',
            'storage_limit_bytes' => 20 * 1024 * 1024 * 1024,
        ]);
    }

    public function test_non_admin_cannot_open_user_access_panel(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }
}
