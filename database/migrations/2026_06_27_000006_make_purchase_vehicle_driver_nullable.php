<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['driver_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->change();
            $table->foreignId('driver_id')->nullable()->change();
            $table->string('guest_reference')->nullable()->after('driver_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['driver_id']);
            $table->dropColumn('guest_reference');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable(false)->change();
            $table->foreignId('driver_id')->nullable(false)->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('driver_id')->references('id')->on('drivers')->cascadeOnDelete();
        });
    }
};
