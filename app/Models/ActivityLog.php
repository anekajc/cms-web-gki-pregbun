<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One successful CMS change, written by the RecordActivity middleware.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_username',
        'menu',
        'action',
        'subject',
        'changes',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            // list<array{label: string, old: mixed, new: mixed}>
            'changes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
