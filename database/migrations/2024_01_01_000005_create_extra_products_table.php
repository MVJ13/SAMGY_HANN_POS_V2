<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // 'Drinks', 'Ice Cream', 'Alcohol', 'Other'
            $table->decimal('price', 8, 2);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_products');
    }
};
