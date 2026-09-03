<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('agencies', 'province_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('province_id')->nullable()->after('address');
            });
        }
        if (!Schema::hasColumn('agencies', 'regency_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('regency_id')->nullable()->after('province_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['regency_id', 'province_id']);
        });
    }
};
