<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangUjian extends Model
{
    use HasFactory;

    protected $table = 'ruang_ujian';

    protected $fillable = [
        'nama',
        'lokasi',
        'kapasitas',
        'is_aktif',
        'lantai',
        'is_prioritas',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'is_prioritas' => 'boolean',
    ];

    /**
     * Scope untuk filter ruang prioritas
     */
    public function scopePrioritas($query)
    {
        return $query->where('is_prioritas', true);
    }

    /**
     * Scope untuk filter berdasarkan lantai
     */
    public function scopeLantai($query, int $lantai)
    {
        return $query->where('lantai', $lantai);
    }
}
