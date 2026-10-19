<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nama',
    'alamat',
    'deskripsi',
    'harga_per_bulan',
    'jumlah_kamar',
    'kamar_tersedia',
])]
class Kos extends Model
{
    protected function casts(): array
    {
        return [
            'harga_per_bulan' => 'decimal:2',
            'jumlah_kamar' => 'integer',
            'kamar_tersedia' => 'integer',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}