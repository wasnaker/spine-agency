<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * agency_jurisdictions — wilayah kerja (kabupaten/kota) milik sebuah Unit.
 *
 * - unit_id: FK ke agencies.id (row type='unit').
 * - regency_id: FK ke regencies.id (wilayah kerja; 1 regency = 1 unit).
 * - UNIQUE(regency_id): satu kab/kota tidak boleh jadi wilayah kerja dua unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_jurisdictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('agencies')->cascadeOnDelete();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->unique('regency_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_jurisdictions');
    }
};
