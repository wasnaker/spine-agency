<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('agencies', 'type')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->string('type', 32)->default('agency')->after('phone');
            });
        }
        if (!Schema::hasColumn('agencies', 'parent_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('type');
            });
        }
        if (!Schema::hasColumn('agencies', 'address')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->string('address', 1024)->nullable()->after('name');
            });
        }
        if (Schema::hasColumn('agencies', 'parent_id') && !$this->hasFk('agencies', 'parent_id')) {
            $hasFK = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='agencies' AND COLUMN_NAME='parent_id' AND TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='agencies'");
            if (empty($hasFK)) {
                Schema::table('agencies', function (Blueprint $table) {
                    $table->foreign('parent_id')->references('id')->on('agencies')->nullOnDelete();
                });
            }
        }

        if (Schema::hasColumn('agencies', 'parent_id')) {
            $hasUnique = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME='agencies' AND CONSTRAINT_TYPE='UNIQUE' AND TABLE_SCHEMA=DATABASE() AND CONSTRAINT_NAME LIKE '%parent_id_code%'");
            if (empty($hasUnique)) {
                Schema::table('agencies', function (Blueprint $table) {
                    $table->dropUnique(['code']);
                    $table->unique(['parent_id', 'code'], 'agencies_parent_id_code_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agencies', 'parent_id')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });
        }
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropUnique('agencies_parent_id_code_unique');
            $table->unique('code');
        });
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['type', 'parent_id', 'address']);
        });
    }

    private function hasFk(string $table, string $column): bool
    {
        $conn = Schema::getConnection();
        $fk = $conn->select(
            "SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );

        return !empty($fk);
    }
};
