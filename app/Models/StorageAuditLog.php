<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public static function log(string $action, ?Media $media = null, ?array $details = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'media_id' => $media?->id,
            'action' => $action,
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
            'details' => $details,
        ]);
    }
}
