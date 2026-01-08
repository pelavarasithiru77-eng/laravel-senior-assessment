<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedInteger('percentage'); 
            $table->boolean('active')->default(true);
            $table->date('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
