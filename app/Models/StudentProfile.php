<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nisn',
    ];

    /**
     * Get the user that owns this student profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
