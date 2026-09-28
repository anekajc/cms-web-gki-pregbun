<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class PembangunanImage extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'public_id',
        'url',
        'order',
    ];

    public function activityLabel(): ?string
    {
        return 'Galeri Pembangunan';
    }
}
