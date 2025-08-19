<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->integer('device_id')->unique();
            $table->string('device_name');
            $table->string('location');
            $table->string('ip_address')->nullable();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
