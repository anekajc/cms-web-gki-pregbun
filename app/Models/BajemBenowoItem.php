<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class BajemBenowoItem extends Model
{
    use RecordsActivity;

    public const SECTION_IBADAH = 'ibadah';

    public const SECTION_PELAYANAN = 'pelayanan';

    public const SECTIONS = [self::SECTION_IBADAH, self::SECTION_PELAYANAN];

    protected $fillable = [
        'section',
        'title',
        'description',
        'schedules',
        'location',
        'audience',
        'cadence',
        'image_public_id',
        'image_url',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'schedules' => 'array',
        ];
    }
}
