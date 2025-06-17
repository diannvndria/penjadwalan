<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPenguji extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penguji',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'deskripsi',
    ];

    // Mengubah kolom 'tanggal' menjadi objek Carbon secara otomatis
    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi: Jadwal Penguji dimiliki oleh satu Penguji.
     */
    public function penguji()
    {
        return $this->belongsTo(Penguji::class, 'id_penguji');
    }
}