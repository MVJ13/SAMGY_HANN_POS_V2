<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // These columns may already exist from the initial migration — skip if so
            if (!Schema::hasColumn('orders', 'amount_received')) {
                $table->decimal('amount_received', 10, 2)->default(0)->after('payment');
            }
            if (!Schema::hasColumn('orders', 'change_given')) {
                $table->decimal('change_given', 10, 2)->default(0)->after('amount_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = array_filter(
                ['amount_received', 'change_given'],
                fn($col) => Schema::hasColumn('orders', $col)
            );
            if ($columns) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
