<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Upload;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
   
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $total = $imported = $updated = $invalid = $duplicates = 0;
        $seenSkus = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $total++;

                $data = array_combine($header, $row);

               
                if (empty($data['sku']) || empty($data['name']) || !isset($data['price'])) {
                    $invalid++;
                    continue;
                }

                
                if (in_array($data['sku'], $seenSkus)) {
                    $duplicates++;
                    continue;
                }

                $seenSkus[] = $data['sku'];

               
                $product = Product::updateOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'name'  => $data['name'],
                        'price' => $data['price']
                    ]
                );

                $product->wasRecentlyCreated ? $imported++ : $updated++;

               
                if (!empty($data['image_checksum'])) {
                    $this->attachImage($product, $data['image_checksum']);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);

        return response()->json([
            'status' => 'success',
            'summary' => compact(
                'total',
                'imported',
                'updated',
                'invalid',
                'duplicates'
            )
        ]);
    }

    
    protected function attachImage(Product $product, string $checksum)
    {
        $upload = Upload::where('checksum', $checksum)
            ->where('status', 'completed')
            ->first();

        if (!$upload) {
            return; 
        }

       
        if (ProductImage::where('product_id', $product->id)
            ->where('upload_id', $upload->id)->exists()) {
            return;
        }

        $originalPath = storage_path("app/public/uploads/{$upload->stored_name}");

        if (!file_exists($originalPath)) {
            return;
        }

        $sizes = [256, 512, 1024];
        $variants = [];

        foreach ($sizes as $size) {
            $img = Image::make($originalPath)
                ->resize($size, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });

            $variantName = "{$size}_{$upload->stored_name}";
            $relativePath = "uploads/variants/{$variantName}";
            $fullPath = storage_path("app/public/{$relativePath}");

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $img->save($fullPath);
            $variants[$size] = $relativePath;
        }

        ProductImage::create([
            'product_id'  => $product->id,
            'upload_id'   => $upload->id,
            'variant_256' => $variants[256],
            'variant_512' => $variants[512],
            'variant_1024'=> $variants[1024],
            'is_primary'  => true
        ]);
    }

 public function importline(Request $request)
    {
                $handle = null;

        try {
           
            $request->validate([
                'file' => 'required|file|mimes:csv,txt'
            ]);

          
            $handle = fopen($request->file('file')->getRealPath(), 'r');

            if (!$handle) {
                throw new \Exception('Unable to open CSV file');
            }

           
            $header = fgetcsv($handle);

            if (!$header) {
                throw new \Exception('CSV header missing');
            }

          
            $total = 0;
            $imported = 0;
            $updated = 0;
            $invalid = 0;
            $duplicates = 0;

            $seenSkus = [];

            DB::beginTransaction();

         
            while (($row = fgetcsv($handle)) !== false) {
                $total++;

            
                if (count($row) !== count($header)) {
                    $invalid++;
                    continue;
                }

                $data = array_combine($header, $row);

             
                if (
                    empty($data['sku']) ||
                    empty($data['name']) ||
                    !isset($data['price'])
                ) {
                    $invalid++;
                    continue;
                }

             
                if (in_array($data['sku'], $seenSkus)) {
                    $duplicates++;
                    continue;
                }

                $seenSkus[] = $data['sku'];

            
                $product = Product::updateOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'name'  => $data['name'],
                        'price' => $data['price']
                    ]
                );

                $product->wasRecentlyCreated ? $imported++ : $updated++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'summary' => [
                    'total_rows' => $total,
                    'imported'   => $imported,
                    'updated'    => $updated,
                    'invalid'    => $invalid,
                    'duplicates' => $duplicates,
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('CSV Import Failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);

        } finally {
          
            if ($handle) {
                fclose($handle);
            }
        }
    }



 public function getUploads()
    {
      
        $uploads = DB::table('uploads')->get();

        return response()->json([
            'message' => 'Uploads fetched successfully',
            'data' => $uploads
        ]);
    }
}
