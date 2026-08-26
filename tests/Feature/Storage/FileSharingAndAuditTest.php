<?php

use App\Enums\UserStatus;
use App\Models\FileShare;
use App\Models\Media;
use App\Models\StorageAuditLog;
use App\Models\User;
use App\Services\FileSharingService;
use App\Services\StorageService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function sharingTestUser(string $role = 'siswa'): User
{
    $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $user->assignRole($role);

    return $user;
}

function signedDownloadUrl(Media $media): string
{
    return URL::temporarySignedRoute('files.download', now()->addMinutes(5), ['media' => $media->id]);
}

test('user A can share a file to user B and user B can download it', function () {
    $owner = sharingTestUser();
    $recipient = sharingTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('shared.pdf', 'shared content'),
    );

    $share = app(FileSharingService::class)->share(
        $owner,
        $media,
        sharedToUserId: $recipient->id,
        permission: 'download',
    );

    expect($share->sharedTo->is($recipient))->toBeTrue()
        ->and($share->isExpired())->toBeFalse();

    $this->actingAs($recipient)->get(signedDownloadUrl($media))->assertOk();
});

test('a user without a matching share is denied download', function () {
    $owner = sharingTestUser();
    $recipient = sharingTestUser();
    $notRecipient = sharingTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('restricted.pdf', 'restricted content'),
    );

    app(FileSharingService::class)->share($owner, $media, $recipient->id, permission: 'download');

    $this->actingAs($notRecipient)->get(signedDownloadUrl($media))->assertForbidden();
});

test('an expired file share is denied', function () {
    $owner = sharingTestUser();
    $recipient = sharingTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('expired.pdf', 'expired content'),
    );

    $share = FileShare::create([
        'media_id' => $media->id,
        'shared_by_user_id' => $owner->id,
        'shared_to_user_id' => $recipient->id,
        'permission' => 'download',
        'expires_at' => now()->subMinute(),
    ]);

    expect($share->isExpired())->toBeTrue();
    $this->actingAs($recipient)->get(signedDownloadUrl($media))->assertForbidden();
});

test('upload and download actions create audit log records', function () {
    $owner = sharingTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('audited.pdf', 'audited content'),
    );

    expect(StorageAuditLog::where('action', 'upload')->where('media_id', $media->id)->exists())->toBeTrue();

    $this->actingAs($owner)->get(signedDownloadUrl($media))->assertOk();

    expect(StorageAuditLog::where('action', 'download')->where('media_id', $media->id)->exists())->toBeTrue();
});

test('soft deleted media enters recycle bin and can be restored', function () {
    $owner = sharingTestUser();
    $media = app(StorageService::class)->upload(
        $owner,
        UploadedFile::fake()->createWithContent('recyclable.pdf', 'recyclable content'),
    );
    $path = $media->getPath();

    app(StorageService::class)->delete($media);

    expect(Media::withTrashed()->find($media->id)->trashed())->toBeTrue()
        ->and(is_file($path))->toBeTrue();

    app(StorageService::class)->restore(Media::withTrashed()->findOrFail($media->id));

    $this->actingAs($owner)->get(signedDownloadUrl($media->fresh()))->assertOk();
    expect(StorageAuditLog::where('action', 'delete')->where('media_id', $media->id)->exists())->toBeTrue()
        ->and(StorageAuditLog::where('action', 'restore')->where('media_id', $media->id)->exists())->toBeTrue();
});
