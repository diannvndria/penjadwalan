<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriMunaqosah extends Model
{
    use HasFactory;

    // Nonaktifkan updated_at, karena hanya ada created_at
    public $timestamps = false;

    protected $fillable = [
        'id_munaqosah',
        'perubahan',
        'dilakukan_oleh',
    ];

    /**
     * Relasi: Histori dimiliki oleh satu Munaqosah.
     */
    public function munaqosah()
    {
        return $this->belongsTo(Munaqosah::class, 'id_munaqosah');
    }

    /**
     * Relasi: Histori dilakukan oleh satu User (admin).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}