<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    // Kolom yang bisa diisi secara massal (mass assignable)
    protected $fillable = [
        'nama',
        'kapasitas_ampu',
    ];

    /**
     * Relasi: Satu Dosen memiliki banyak Mahasiswa.
     */
    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'id_dospem');
    }

    /**
     * Accessor: Menghitung jumlah mahasiswa yang sedang diampu.
     * Bisa diakses sebagai $dosen->jumlah_diampu_sekarang
     */
    public function getJumlahDiampuSekarangAttribute()
    {
        return $this->mahasiswas()->count();
    }
}