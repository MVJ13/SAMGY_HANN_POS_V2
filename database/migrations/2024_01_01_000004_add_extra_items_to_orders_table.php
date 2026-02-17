<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Stores line items: [['name'=>'Soju','price'=>150,'qty'=>2,'amount'=>300], ...]
            $table->json('extra_items')->nullable()->after('addons');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('extra_items');
        });
    }
};
