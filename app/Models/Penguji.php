<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penguji extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'is_prioritas',
        'keterangan_prioritas',
    ];

    protected $casts = [
        'is_prioritas' => 'boolean',
    ];

    public function jadwalPengujis()
    {
        return $this->hasMany(JadwalPenguji::class, 'id_penguji');
    }

    public function munaqosahsAsPenguji1()
    {
        return $this->hasMany(Munaqosah::class, 'id_penguji1');
    }

    public function munaqosahsAsPenguji2()
    {
        return $this->hasMany(Munaqosah::class, 'id_penguji2');
    }

    /**
     * Helper method untuk cek status prioritas
     */
    public function isPrioritas(): bool
    {
        return $this->is_prioritas;
    }

    // Pastikan tidak ada relasi munaqosahsAsPengujiUtama()
    // public function munaqosahsAsPengujiUtama() { return $this->hasMany(Munaqosah::class, 'id_penguji_utama'); }
}
