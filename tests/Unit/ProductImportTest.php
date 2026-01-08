<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ProductImportTest extends TestCase
{

     use RefreshDatabase;
    public function testProductUpsert()
    {
       
        $product = Product::factory()->create([
            'sku' => 'SKU123',
            'name' => 'Old Name'
        ]);

      
        $data = ['sku' => 'SKU123', 'name' => 'New Name'];
        Product::updateOrCreate(['sku' => $data['sku']], $data);

      
        $this->assertDatabaseHas('products', [
            'sku' => 'SKU123',
            'name' => 'New Name'
        ]);
    }
}
