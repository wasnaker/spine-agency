<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('agencies', 'admin_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('parent_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('admin_id');
        });
    }
};
