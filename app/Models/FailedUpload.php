<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedUpload extends Model
{
    use HasFactory;
    protected $guarded = []; // Agar bisa diisi massal
}
