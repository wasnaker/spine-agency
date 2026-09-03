<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename kolom `parent` -> `parent_id` (agencies).
 * Konvensi: kolom FK relasi selalu suffix _id; nama kolom tidak boleh
 * sama dengan nama method relasi (parent()) — attribute menimpa relasi
 * saat serialisasi, jadi ->with('parent') tak pernah muncul.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('agencies', 'parent') || Schema::hasColumn('agencies', 'parent_id')) {
            return;
        }

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropForeign('agencies_parent_foreign');
            $table->dropUnique('agencies_parent_code_unique');
            $table->renameColumn('parent', 'parent_id');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('agencies')->nullOnDelete();
            $table->unique(['parent_id', 'code'], 'agencies_parent_id_code_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('agencies', 'parent_id') || Schema::hasColumn('agencies', 'parent')) {
            return;
        }

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropForeign('agencies_parent_id_foreign');
            $table->dropUnique('agencies_parent_id_code_unique');
            $table->renameColumn('parent_id', 'parent');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreign('parent')->references('id')->on('agencies')->nullOnDelete();
            $table->unique(['parent', 'code']);
        });
    }
};
