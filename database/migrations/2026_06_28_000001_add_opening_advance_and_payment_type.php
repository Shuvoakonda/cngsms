<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->decimal('opening_advance', 14, 2)->default(0)->after('opening_balance');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('type')->default('payment')->after('pump_id');
        });
    }

    public function down(): void
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->dropColumn('opening_advance');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
