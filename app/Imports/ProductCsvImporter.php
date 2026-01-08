<?php

namespace App\Imports;

use App\Models\Product;

class ProductCsvImporter
{
    public function import(string $filePath): array
    {
        $imported = 0;
        $updated = 0;
        $invalid = 0;

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $rows = array_map('str_getcsv', file($filePath));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            if (empty($data['sku']) || empty($data['name'])) {
                $invalid++;
                continue;
            }

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                ['name' => $data['name'], 'price' => $data['price']]
            );

            if ($product->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'invalid' => $invalid
        ];
    }
}
