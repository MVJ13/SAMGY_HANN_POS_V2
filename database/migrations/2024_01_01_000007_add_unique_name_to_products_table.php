<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any existing duplicates before adding the constraint —
        // keep the lowest id for each name, delete the rest.
        $dupes = DB::table('products')
            ->select('name', DB::raw('MIN(id) as keep_id'))
            ->groupBy('name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($dupes as $dupe) {
            DB::table('products')
                ->where('name', $dupe->name)
                ->where('id', '!=', $dupe->keep_id)
                ->delete();
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
