<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections') && ! Schema::hasColumn('sections', 'year_level')) {
            Schema::table('sections', function (Blueprint $table) {
                // Nullable: existing sections were created before year level was
                // tracked, and the chair backfills them from the Sections page.
                $table->unsignedTinyInteger('year_level')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'year_level')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('year_level');
            });
        }
    }
};
