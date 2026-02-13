<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedUpload extends Model
{
    use HasFactory;
    // Kolom yang diizinkan untuk mass assignment
    protected $fillable = [
        'image_path',
        'reason',
    ];
}
