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
    ];
}


