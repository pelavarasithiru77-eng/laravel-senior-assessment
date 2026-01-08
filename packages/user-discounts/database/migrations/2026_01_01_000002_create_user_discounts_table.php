<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_discounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('discount_id')
                  ->constrained('discounts')
                  ->cascadeOnDelete();

            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('usage_cap')->default(1);

            $table->timestamps();

           
            $table->unique(['user_id', 'discount_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_discounts');
    }
};
