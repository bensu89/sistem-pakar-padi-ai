<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciLog extends Model
{
    use HasFactory;

    protected $table = 'pohaci_logs';

    // Izinkan semua kolom diisi (Mass Assignment)
    protected $guarded = ['id'];

    // Cast metadata jadi array otomatis biar enak dipakai
    protected $casts = [
        'meta_data' => 'array',
    ];
}