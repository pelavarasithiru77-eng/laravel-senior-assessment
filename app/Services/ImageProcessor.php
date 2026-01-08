<?php

namespace App\Services;
use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageProcessor
{
    protected ImageManager $manager;

    public function __construct()
    {
       
        $this->manager = new ImageManager('gd');
    }

    
    public function process(UploadedFile $file): array
    {
        $variants = [256, 512, 1024];
        $paths = [];

        foreach ($variants as $size) {
            $img = $this->manager->make($file)
                ->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

            $path = "images/{$size}_" . $file->hashName();
            Storage::disk('public')->put($path, (string) $img->encode());

            $paths[$size] = $path;
        }

        return $paths;
    }
}
