<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PohaciConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
