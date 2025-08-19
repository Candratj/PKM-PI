<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captured_images', function (Blueprint $table) {
            $table->id();
            $table->integer('device_id');
            $table->string('image_path');
            $table->timestamp('captured_at');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captured_images');
    }
};
