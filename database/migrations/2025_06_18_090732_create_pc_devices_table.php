<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pc_devices', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->foreignId('pc_assignments_id')->nullable()->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('device_name')->nullable();
            $table->string('mac_address')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_devices');
    }
};
