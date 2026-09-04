<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_staffs', function (Blueprint $table) {
            $table->id();

            // Profil staff terhubung 1:1 ke user login (username/email di users,
            // realname/nip/jabatan di sini).
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Kantor dinas (type=agency) atau unit (type=unit) tempat staff bertugas.
            $table->foreignId('agency_id')
                ->constrained('agencies')
                ->cascadeOnDelete();

            // Unit tempat bertugas; null = bertugas di pusat (Disnaker HO).
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('agencies')
                ->nullOnDelete();

            $table->string('realname');      // nama asli untuk optionlist/dropdown
            $table->string('nip', 50)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_staffs');
    }
};
