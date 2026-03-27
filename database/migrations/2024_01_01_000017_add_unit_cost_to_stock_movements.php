<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Stores the product's cost price at the exact moment a stock movement occurs.
// This makes COGS historically accurate — changing a product's cost later
// will no longer retroactively alter past P&L figures.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->after('quantity')
                  ->comment('Cost price per unit at time of movement — locked in for accurate historical COGS');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
