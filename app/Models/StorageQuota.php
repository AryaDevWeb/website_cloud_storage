<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StorageQuota extends Model
{
    use HasFactory;

    public const STUDENT_DEFAULT_BYTES = 104857600;

    public const TEACHER_DEFAULT_BYTES = 1073741824;

    protected $fillable = [
        'user_id',
        'max_bytes',
        'used_bytes',
    ];

    protected function casts(): array
    {
        return [
            'max_bytes' => 'integer',
            'used_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function defaultMaxBytesFor(User $user): int
    {
        return $user->isGuru()
            ? self::TEACHER_DEFAULT_BYTES
            : self::STUDENT_DEFAULT_BYTES;
    }

    public function hasAvailableSpace(int $bytes): bool
    {
        return $bytes >= 0 && $this->used_bytes + $bytes <= $this->max_bytes;
    }

    public function updateUsage(int $bytesToAddOrSubtract): void
    {
        $newUsage = max(0, $this->used_bytes + $bytesToAddOrSubtract);

        if ($newUsage > $this->max_bytes) {
            throw ValidationException::withMessages([
                'file' => 'Kuota penyimpanan tidak mencukupi untuk operasi ini.',
            ]);
        }

        $this->forceFill(['used_bytes' => $newUsage])->save();
    }
}
