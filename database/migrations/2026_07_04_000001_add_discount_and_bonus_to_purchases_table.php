<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('discount_value', 12, 2)->nullable()->after('rate');
            $table->string('discount_type', 20)->nullable()->after('discount_value');
            $table->decimal('bonus_value', 12, 2)->nullable()->after('discount_type');
            $table->string('bonus_type', 20)->nullable()->after('bonus_value');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'discount_value',
                'discount_type',
                'bonus_value',
                'bonus_type',
            ]);
        });
    }
};
