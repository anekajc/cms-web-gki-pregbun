<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class PembangunanVideo extends Model
{
    use RecordsActivity;

    protected $fillable = [
        'youtube_url',
        'youtube_embed_url',
    ];

    public function activityLabel(): ?string
    {
        return 'Video YouTube';
    }
}
