<?php

namespace App\Services;

use App\Models\Product;

class ProductCsvImporter
{
    public function import(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        $total = $imported = $updated = $invalid = $duplicates = 0;
        $seenSkus = [];

        while (($row = fgetcsv($handle)) !== false) {
            $total++;

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
                    'price' => $data['price'],
                ]
            );

            if ($product->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
        }

        fclose($handle);

        return [
            'total'      => $total,
            'imported'   => $imported,
            'updated'    => $updated,
            'invalid'    => $invalid,
            'duplicates' => $duplicates,
        ];
    }
}
