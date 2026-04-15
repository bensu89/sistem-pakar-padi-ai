<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'mode',
        'risk_level',
        'result_text',
        'fertilizer_suggestion',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];
}
