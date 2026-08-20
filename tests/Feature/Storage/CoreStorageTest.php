<?php

use App\Enums\UserStatus;
use App\Models\Folder;
use App\Models\StorageQuota;
use App\Models\User;
use App\Services\StorageService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function storageTestUser(string $role = 'siswa'): User
{
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $user->assignRole($role);

    return $user;
}

test('uploading a file stores it privately and updates quota usage', function () {
    $user = storageTestUser();
    $file = UploadedFile::fake()->createWithContent('report.pdf', str_repeat('A', 128));

    $media = app(StorageService::class)->upload($user, $file);

    expect($media->disk)->toBe('local')
        ->and($media->folder_id)->toBeNull()
        ->and($media->model_id)->toBe($user->id)
        ->and($user->storageQuota->used_bytes)->toBe($media->size);
});

test('upload fails when the file exceeds the remaining quota', function () {
    $user = storageTestUser();
    $quota = StorageQuota::create([
        'user_id' => $user->id,
        'max_bytes' => 5,
        'used_bytes' => 0,
    ]);
    $file = UploadedFile::fake()->createWithContent('too-large.pdf', str_repeat('A', 10));

    expect(fn () => app(StorageService::class)->upload($user, $file))
        ->toThrow(ValidationException::class);

    expect($quota->fresh()->used_bytes)->toBe(0)
        ->and($user->media()->count())->toBe(0);
});

test('private media is not available through a public storage URL', function () {
    $user = storageTestUser();
    $media = app(StorageService::class)->upload(
        $user,
        UploadedFile::fake()->createWithContent('private.pdf', 'private content'),
    );

    $response = $this->get('/storage/'.$media->file_name);

    expect([403, 404])->toContain($response->status());
});

test('only the owner can download media through a valid signed URL', function () {
    $owner = storageTestUser();
    $otherUser = storageTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('owned.pdf', 'owned content'),
    );
    $url = URL::temporarySignedRoute('files.download', now()->addMinutes(5), ['media' => $media->id]);

    $this->actingAs($owner)->get($url)->assertOk();
    $this->actingAs($otherUser)->get($url)->assertForbidden();
});

test('nested folders preserve ownership and parent hierarchy', function () {
    $user = storageTestUser();
    $root = Folder::create([
        'user_id' => $user->id, 'name' => 'Dokumen', 'slug' => 'dokumen',
    ]);
    $child = Folder::create([
        'user_id' => $user->id, 'parent_id' => $root->id,
        'name' => 'Laporan', 'slug' => 'laporan',
    ]);

    expect($child->parent->is($root))->toBeTrue()
        ->and($root->children->first()->is($child))->toBeTrue()
        ->and($child->user_id)->toBe($user->id);
});
