<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Re-declare the status ENUM to ensure all three values exist.
        // This fixes databases created from older migrations that may have
        // had only 'active'/'completed', causing Data truncated errors on 'cancelled'.
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");
    }
};
