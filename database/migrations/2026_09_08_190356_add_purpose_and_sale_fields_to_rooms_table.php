<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds "Sell" feature fields to rooms table:
     * - purpose: 'rent' (default) or 'sell'
     * - price: total sale price for sell listings
     * - possession_status: ready_to_move / under_construction
     * - ownership_type: Freehold / Leasehold / Power of Attorney
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Purpose: rent (default) or sell
            $table->enum('purpose', ['rent', 'sell'])->default('rent')->after('rent');

            // Sale price (total) — only used when purpose = 'sell'
            $table->bigInteger('price')->nullable()->unsigned()->after('purpose');

            // Possession status — for sell listings
            $table->enum('possession_status', ['ready_to_move', 'under_construction'])
                  ->nullable()
                  ->after('price');

            // Ownership type — for sell listings
            $table->string('ownership_type', 100)->nullable()->after('possession_status');

            // Index for fast purpose-based search queries
            $table->index(['purpose', 'status', 'city'], 'idx_rooms_purpose_status_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('idx_rooms_purpose_status_city');
            $table->dropColumn(['purpose', 'price', 'possession_status', 'ownership_type']);
        });
    }
};
