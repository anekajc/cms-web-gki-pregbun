<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class HomeSetting extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'video_16x9_public_id',
        'video_16x9_url',
        'video_4x3_public_id',
        'video_4x3_url',
    ];

    /**
     * There is only ever one settings row. Fetch it, creating it on first use.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function activityLabel(): ?string
    {
        return 'Video Home';
    }
}
