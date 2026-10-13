<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class BidangPelayanan extends Model
{
    use RecordsActivity;

    protected $table = 'bidang_pelayanan';

    protected $fillable = [
        'name',
        'pic_name',
        'pic_phone',
        'order',
    ];
}
