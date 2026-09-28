<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class PembangunanUpdate extends Model
{
    use RecordsActivity;

    protected $table = 'pembangunan_updates';

    protected $fillable = [
        'update_date',
        'target_persembahan',
        'persembahan_pembangunan',
        'janji_iman_terealisasi',
        'janji_iman_belum_terealisasi',
        'rincian_start_date',
        'rincian_end_date',
    ];

    protected function casts(): array
    {
        return [
            'update_date' => 'date',
            'rincian_start_date' => 'date',
            'rincian_end_date' => 'date',
            'target_persembahan' => 'integer',
            'persembahan_pembangunan' => 'integer',
            'janji_iman_terealisasi' => 'integer',
            'janji_iman_belum_terealisasi' => 'integer',
        ];
    }

    public function activityLabel(): ?string
    {
        return 'Dana Pembangunan '.$this->update_date?->format('d/m/Y');
    }
}
