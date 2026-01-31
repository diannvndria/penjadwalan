<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Munaqosah extends Model
{
    use HasFactory;

    protected $table = 'munaqosah';

    protected $fillable = [
        'id_mahasiswa',
        'tanggal_munaqosah',
        'waktu_mulai',
        'waktu_selesai',
        'id_penguji1',
        'id_penguji2', // Hanya ini
        'id_ruang_ujian',
        'status_konfirmasi',
    ];

    protected $casts = [
        'tanggal_munaqosah' => 'date',
    ];

    /**
     * Boot method to clear cache when munaqosah data changes
     */
    protected static function booted()
    {
        // Clear dashboard cache when schedule is created, updated, or deleted
        static::created(function () {
            Cache::forget('dashboard_stats');
        });

        static::updated(function ($munaqosah) {
            Cache::forget('dashboard_stats');

            // If status changed to confirmed or rejected, update dashboard
            if ($munaqosah->wasChanged('status_konfirmasi')) {
                Cache::forget('dashboard_stats');
            }
        });

        static::deleted(function () {
            Cache::forget('dashboard_stats');
        });
    }

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

    public function ruangUjian()
    {
        return $this->belongsTo(RuangUjian::class, 'id_ruang_ujian');
    }
}
