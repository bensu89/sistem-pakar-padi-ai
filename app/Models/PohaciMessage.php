<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'sender_type',
        'content',
        'has_attachment',
        'metadata',
    ];

    protected $casts = [
        'has_attachment' => 'boolean',
        'metadata' => 'array',
    ];
}
