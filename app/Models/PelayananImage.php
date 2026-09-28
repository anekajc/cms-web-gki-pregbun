<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelayananImage extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'pelayanan_id',
        'public_id',
        'url',
        'order',
    ];

    public function pelayanan(): BelongsTo
    {
        return $this->belongsTo(Pelayanan::class);
    }

    public function activityLabel(): ?string
    {
        return $this->pelayanan?->title;
    }
}
