<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'angkatan',
        'judul_skripsi',
        'profil_lulusan', // Pastikan ini ada
        'penjurusan',     // Pastikan ini ada
        'id_dospem',
        'siap_sidang',
        'is_prioritas',
        'prioritas_jadwal',
        'keterangan_prioritas',
    ];

    protected $casts = [
        'siap_sidang' => 'boolean',
        'is_prioritas' => 'boolean',
        'prioritas_jadwal' => 'boolean',
    ];

    /**
     * Boot method to clear cache when mahasiswa data changes
     */
    protected static function booted()
    {
        // Clear dashboard cache when mahasiswa is created, updated, or deleted
        static::created(function () {
            Cache::forget('dashboard_stats');
        });

        static::updated(function ($mahasiswa) {
            Cache::forget('dashboard_stats');

            // If siap_sidang status changed, also clear batch pengujis cache
            if ($mahasiswa->wasChanged('siap_sidang')) {
                Cache::forget('batch_schedule_pengujis');
            }
        });

        static::deleted(function () {
            Cache::forget('dashboard_stats');
            Cache::forget('batch_schedule_pengujis');
        });
    }

    public function dospem()
    {
        return $this->belongsTo(Dosen::class, 'id_dospem');
    }

    public function munaqosah()
    {
        return $this->hasOne(Munaqosah::class, 'id_mahasiswa');
    }

    /**
     * Helper method untuk cek status prioritas RUANG
     * Returns true ONLY if student has is_prioritas flag (room priority)
     * This is used for room allocation (ground floor/accessible rooms)
     */
    public function isPrioritas(): bool
    {
        return (bool) $this->is_prioritas;
    }

    /**
     * Helper method untuk cek prioritas JADWAL
     * Returns true ONLY if student has prioritas_jadwal flag
     * This is used for scheduling order (scheduled first)
     */
    public function isPrioritasJadwal(): bool
    {
        return (bool) $this->prioritas_jadwal;
    }

    /**
     * Check if student has any priority flag (for display purposes)
     */
    public function hasAnyPriority(): bool
    {
        return $this->is_prioritas || $this->prioritas_jadwal;
    }
}
