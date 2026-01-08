<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
        use HasFactory; 
    protected $fillable = ['sku', 'name', 'price','description'];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(Image::class)->where('is_primary', 1);
    }
}
