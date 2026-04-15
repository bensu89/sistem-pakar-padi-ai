<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'latitude',
        'longitude',
        'source',
        'confidence',
        'label',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
