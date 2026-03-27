<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fix #3: Store product_id on extra_items so stock reversal on cancellation
// can match by ID rather than by name (resilient to product renames)
// Note: extra_items is a JSON column — the product_id is stored inside the JSON.
// This migration documents the intent; no schema change needed as JSON is already flexible.
// The actual fix is in NewOrder.php (store id) and Receipts.php (match by id).
return new class extends Migration
{
    public function up(): void
    {
        // No schema change required — extra_items is already a JSON column.
        // product_id is now included in each item object within that JSON.
    }

    public function down(): void {}
};
