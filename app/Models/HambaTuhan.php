<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class HambaTuhan extends Model
{
    use RecordsActivity;

    protected $table = 'hamba_tuhan';

    protected $fillable = [
        'name',
        'description',
        'image_public_id',
        'image_url',
        'order',
    ];
}
