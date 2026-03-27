<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('label');
            $table->string('type')->default('number'); // number, text
            $table->timestamps();
        });

        // Seed default package prices
        // insertOrIgnore prevents duplicate key errors on re-migration
        DB::table('settings')->insertOrIgnore([
            ['key' => 'price_basic',   'value' => '199', 'label' => 'Basic Package Price',   'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_premium', 'value' => '269', 'label' => 'Premium Package Price', 'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_deluxe',  'value' => '349', 'label' => 'Deluxe Package Price',  'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_addon',   'value' => '25',  'label' => 'Add-on Price (per pax)', 'type' => 'number', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
