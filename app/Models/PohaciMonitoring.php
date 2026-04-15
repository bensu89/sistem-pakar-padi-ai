<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reporter_name',
        'reporter_email',
        'image_path',
        'latitude',
        'longitude',
        'coordinate_source',
        'location_label',
        'disease_name',
        'confidence',
        'solution',
        'ndvi_value',
        'satellite_source',
        'analysis_mode',
        'recommendation',
        'followup_status',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
