<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number');
            $table->string('registration_number')->nullable();
            $table->string('type')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('vehicle_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
