<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();

            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('checksum')->index();

            $table->unsignedInteger('total_chunks');
            $table->unsignedInteger('uploaded_chunks')->default(0);

            $table->enum('status', ['pending', 'completed', 'failed'])
                  ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
