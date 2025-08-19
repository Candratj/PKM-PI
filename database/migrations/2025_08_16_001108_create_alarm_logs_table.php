<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alarm_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('device_id');
            $table->timestamp('activated_at');
            $table->timestamp('deactivated_at')->nullable();
            $table->enum('status', ['activated', 'deactivated']);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alarm_logs');
    }
};
