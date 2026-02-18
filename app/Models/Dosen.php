<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    // Kolom yang bisa diisi secara massal (mass assignable)
    protected $fillable = [
        'nip',
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
     * Relasi: Dosen bisa mengakses Munaqosah melalui Mahasiswa yang dibimbing.
     * Ini digunakan untuk menghitung "Riwayat Ketua Sidang".
     */
    public function munaqosahs()
    {
        return $this->hasManyThrough(Munaqosah::class, Mahasiswa::class, 'id_dospem', 'id_mahasiswa');
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
