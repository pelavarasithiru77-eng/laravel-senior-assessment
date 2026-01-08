<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'checksum',
        'original_name',
        'total_chunks',
        'received_chunks',
        'status',
        'final_path',
    ];

    protected $casts = [
        'received_chunks' => 'array',
    ];
}
