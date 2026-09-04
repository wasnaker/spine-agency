<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index type pada agencies — WHERE type='unit'/'agency' dipakai di
 * units/staffs/companies/jurisdictions (ditemukan full table scan 25rb
 * via performance_schema, 2026-09-05).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });
    }
};
