<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->date('purchase_date');
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pump_id')->constrained()->cascadeOnDelete();
            $table->string('slip_number');
            $table->decimal('quantity', 12, 2);
            $table->decimal('rate', 12, 2);
            $table->decimal('amount', 14, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pump_id', 'slip_number']);
            $table->index('purchase_date');
            $table->index(['pump_id', 'purchase_date']);
            $table->index(['vehicle_id', 'purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
