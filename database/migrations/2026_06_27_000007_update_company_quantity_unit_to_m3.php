<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('quantity_unit', 10)->default('M3')->change();
        });

        DB::table('companies')
            ->whereRaw('UPPER(quantity_unit) = ?', ['KG'])
            ->update(['quantity_unit' => 'M3']);
    }

    public function down(): void
    {
        DB::table('companies')
            ->where('quantity_unit', 'M3')
            ->update(['quantity_unit' => 'KG']);

        Schema::table('companies', function (Blueprint $table) {
            $table->string('quantity_unit', 10)->default('KG')->change();
        });
    }
};
