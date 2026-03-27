<?php

use Illuminate\Database\Migrations\Migration;

// This migration is intentionally schema-free.
// extra_items is a JSON column. Each item entry now includes a 'cost_price' field
// captured at time of sale (from product->cost at that moment) so historical P&L
// remains accurate even if the product's cost is updated later.
// The actual change is in NewOrder::syncAndCreate (PHP side).
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
