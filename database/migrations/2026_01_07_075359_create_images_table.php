<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the table first to avoid duplicate table errors (safe for tests)
        Schema::dropIfExists('images');

        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Foreign key to uploads table (nullable if needed)
            $table->foreignId('upload_id')->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            // Foreign key to products table (nullable)
            $table->foreignId('product_id')->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('path');              // image path
            $table->string('variant');           // original | 256 | 512 | 1024
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_primary')->default(false); // primary image flag

            $table->timestamps();

            // Prevent duplicate variants per upload
            $table->unique(['upload_id', 'variant']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
