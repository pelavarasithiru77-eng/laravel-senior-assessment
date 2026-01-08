<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('upload_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('variant_256');
            $table->string('variant_512');
            $table->string('variant_1024');

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            // Prevent duplicate attachment (idempotent)
            $table->unique(['product_id', 'upload_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
