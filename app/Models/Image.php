<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'upload_id', 'product_id', 'path', 'variant', 'width', 'height', 'is_primary'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
