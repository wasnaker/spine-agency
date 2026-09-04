<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registrasi surveyor (HO) ke agency (Disnaker) — kerja lintas dinas.
     *
     * 1 baris per (agency, surveyor HO): cabang otomatis tercakup saat
     * approved (keputusan 4 Sep 2026). Status: pending|approved|rejected|review.
     * Rejected/review bisa di-register ulang (status balik pending).
     */
    public function up(): void
    {
        Schema::create('agency_surveyor_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            // Selalu row HO (type=surveyor) — branch adalah row self-ref.
            $table->foreignId('surveyor_id')->constrained('surveyors')->cascadeOnDelete();
            $table->string('status', 16)->default('pending'); // pending|approved|rejected|review
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->string('note', 1024)->nullable(); // alasan reject / catatan review
            $table->timestamps();

            $table->unique(['agency_id', 'surveyor_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_surveyor_registrations');
    }
};
