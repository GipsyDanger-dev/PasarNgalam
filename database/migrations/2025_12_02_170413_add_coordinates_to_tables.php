<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NOTE: This migration was previously trying to alter a non-existent 'tables' table.
     * The actual coordinate columns are now handled by 2025_12_04_044018_add_location_columns_to_users_table.php
     * This migration is now a no-op and can be safely skipped.
     */
    public function up(): void
    {
        // No-op: coordinate columns are added in the newer migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: coordinate columns are dropped in the newer migration
    }
};