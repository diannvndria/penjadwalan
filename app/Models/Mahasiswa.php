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
        'profil_lulusan', // Pastikan ini ada
        'penjurusan',     // Pastikan ini ada
        'id_dospem',
        'siap_sidang',
    ];

    public function dospem()
    {
        return $this->belongsTo(Dosen::class, 'id_dospem');
    }

    public function munaqosah()
    {
        return $this->hasOne(Munaqosah::class, 'id_mahasiswa');
    }
}
