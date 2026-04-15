<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciSatelliteObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'location_id',
        'satellite_source',
        'ndvi_value',
        'captured_from',
        'captured_to',
        'raw_payload',
    ];

    protected $casts = [
        'captured_from' => 'date',
        'captured_to' => 'date',
        'raw_payload' => 'array',
    ];
}
