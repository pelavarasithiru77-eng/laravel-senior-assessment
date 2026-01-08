<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'upload_id',
        'variant_256',
        'variant_512',
        'variant_1024',
        'is_primary'
    ];
}
