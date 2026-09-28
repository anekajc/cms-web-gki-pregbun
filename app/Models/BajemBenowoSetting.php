<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class BajemBenowoSetting extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'about_description',
        'about_image_public_id',
        'about_image_url',
        'pelayanan_intro',
        'address',
        'maps_url',
        'map_embed_url',
        'location_image_public_id',
        'location_image_url',
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
        return 'Pengaturan Bajem Benowo';
    }
}
