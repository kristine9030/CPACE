<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subjects', 'color')) {
            Schema::table('subjects', fn (Blueprint $table) => $table->string('color', 20)->nullable());
        }
        if (! Schema::hasColumn('subjects', 'is_active')) {
            Schema::table('subjects', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        }
        if (! Schema::hasColumn('topics', 'sort_order')) {
            Schema::table('topics', fn (Blueprint $table) => $table->unsignedInteger('sort_order')->default(0));
        }
        if (! Schema::hasColumn('topics', 'is_active')) {
            Schema::table('topics', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        }
    }

    /** Existing installations differ, so rollback must not drop base-schema columns. */
    public function down(): void
    {
        // Intentionally left in place to preserve curriculum status data.
    }
};
