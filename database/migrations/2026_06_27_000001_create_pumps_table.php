<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pumps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pumps');
    }
};
