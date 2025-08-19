<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motion_detections', function (Blueprint $table) {
            $table->id();
            $table->integer('device_id');
            $table->timestamp('detected_at');
            $table->string('alert_type')->default('motion_detected');
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motion_detections');
    }
};
