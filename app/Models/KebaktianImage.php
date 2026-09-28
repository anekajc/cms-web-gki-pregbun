<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KebaktianImage extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'kebaktian_id',
        'public_id',
        'url',
        'order',
    ];

    public function kebaktian(): BelongsTo
    {
        return $this->belongsTo(Kebaktian::class);
    }

    public function activityLabel(): ?string
    {
        return $this->kebaktian?->title;
    }
}
