<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Warta extends Model
{
    use RecordsActivity;

    protected $table = 'warta';

    protected $fillable = [
        'service_date',
        'title',
        'source_url',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
        ];
    }

    public function activityLabel(): ?string
    {
        return $this->title ?: 'Warta '.$this->service_date?->format('d/m/Y');
    }

    /** `url` is the embed link derived from source_url. */
    public function activityIgnoredFields(): array
    {
        return ['url'];
    }
}
