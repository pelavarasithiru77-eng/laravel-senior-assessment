<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    public function attach(Request $request)
    {
        try {
        Log::info('Attach API called', $request->all());

      
        $request->validate([
            'sku'             => 'required|string',
            'upload_checksum' => 'required|string',
            'primary'         => 'boolean'
        ]);

      
        $product = DB::table('products')
            ->where('sku', $request->sku)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

       
        $upload = DB::table('uploads')
            ->where('checksum', $request->upload_checksum)
            ->where('status', 'completed')
            ->first();

        if (!$upload) {
            return response()->json(['message' => 'Upload not found or not completed'], 404);
        }

       
        $extension = strtolower(pathinfo($upload->stored_name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            return response()->json(['message' => 'Uploaded file is not an image'], 422);
        }

       
        $originalPath = storage_path('app/public/uploads/' . $upload->stored_name);

        if (!file_exists($originalPath)) {
            return response()->json(['message' => 'Image file missing'], 404);
        }

      
        $manager = new ImageManager(new Driver());

       
        $sizes = [
            'variant_256'  => [256, 256],
            'variant_512'  => [512, 512],
            'variant_1024' => [1024, 1024],
        ];

        $paths = [];

        foreach ($sizes as $key => [$w, $h]) {

            $image = $manager->read($originalPath)
                ->scaleDown($w, $h);

            $fileName = $key . '_' . $upload->stored_name;
            $savePath = 'products/' . $fileName;

            Storage::disk('public')->put(
                $savePath,
                (string) $image->encode()
            );

            $paths[$key] = $savePath;
        }

        DB::beginTransaction();

      
        if ($request->primary) {
            DB::table('product_images')
                ->where('product_id', $product->id)
                ->update(['is_primary' => 0]);
        }

       
        DB::table('product_images')->insert([
            'product_id'   => $product->id,
            'upload_id'    => $upload->id,
            'variant_256'  => $paths['variant_256'],
            'variant_512'  => $paths['variant_512'],
            'variant_1024' => $paths['variant_1024'],
            'is_primary'   => $request->primary ?? 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Image attached successfully'
        ], 201);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Attach API error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'message' => 'Server Error',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
