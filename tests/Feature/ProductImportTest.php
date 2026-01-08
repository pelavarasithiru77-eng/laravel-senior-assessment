<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Services\ProductCsvImporter;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_upsert()
    {
        Product::create([
            'sku' => 'SKU1',
            'name' => 'Old',
            'price' => 10,
        ]);

        $importer = new ProductCsvImporter();

        $stats = $importer->import(
            base_path('tests/data/products.csv')
        );

        $this->assertEquals(1, $stats['updated']);
    }
}
