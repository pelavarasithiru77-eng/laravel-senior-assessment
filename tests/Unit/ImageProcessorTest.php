<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageProcessorTest extends TestCase
{
    public function test_image_variants_are_generated()
    {
        
        Storage::fake('public');

     
        $file = UploadedFile::fake()->image('photo.jpg', 2000, 1200);

        
        $processor = new ImageProcessor();
        $variants = $processor->process($file);

      
        $this->assertCount(3, $variants);

        foreach ([256, 512, 1024] as $size) {
            Storage::disk('public')->assertExists($variants[$size]);
        }
    }
}
