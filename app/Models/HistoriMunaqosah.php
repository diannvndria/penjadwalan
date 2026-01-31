<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriMunaqosah extends Model
{
    use HasFactory;

    protected $table = 'histori_munaqosah';

    // Disable updated_at, hanya gunakan created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id_munaqosah',
        'perubahan',
        'dilakukan_oleh',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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
