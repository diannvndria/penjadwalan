<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim',
        'nama',
        'angkatan',
        'judul_skripsi',
        'id_dospem',
        'siap_sidang',
    ];

    /**
     * Relasi: Mahasiswa dimiliki oleh satu Dosen (pembimbing).
     */
    public function dospem()
    {
        return $this->belongsTo(Dosen::class, 'id_dospem');
    }

    /**
     * Relasi: Satu Mahasiswa memiliki satu jadwal Munaqosah.
     */
    public function munaqosah()
    {
        return $this->hasOne(Munaqosah::class, 'id_mahasiswa');
    }
}