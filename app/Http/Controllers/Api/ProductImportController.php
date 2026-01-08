<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductCsvImporter;
use Illuminate\Http\Request;

class ProductImportController
{
    public function import(Request $request, ProductCsvImporter $importer)
    {
        $path = $request->file('csv')->store('imports');
        return response()->json(
            $importer->import(storage_path("app/$path"))
        );
    }
}

