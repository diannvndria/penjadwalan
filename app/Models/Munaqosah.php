<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Munaqosah extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_mahasiswa',
        'tanggal_munaqosah',
        'waktu_mulai',
        'waktu_selesai',
        'id_penguji1',
        'id_penguji2', // Hanya ini
        'status_konfirmasi',
    ];

    protected $casts = [
        'tanggal_munaqosah' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    public function penguji1()
    {
        return $this->belongsTo(Penguji::class, 'id_penguji1');
    }

    public function penguji2()
    {
        return $this->belongsTo(Penguji::class, 'id_penguji2');
    }

    // Pastikan tidak ada relasi pengujiUtama()
    // public function pengujiUtama() { return $this->belongsTo(Penguji::class, 'id_penguji_utama'); }

    public function historiPerubahan()
    {
        return $this->hasMany(HistoriMunaqosah::class, 'id_munaqosah');
    }
}
