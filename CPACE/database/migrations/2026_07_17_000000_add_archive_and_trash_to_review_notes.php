<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archived tab + Trash folder for Review Notes: archiving hides a note from
     * the main lists without deleting it, and deletes become soft (Trash) so
     * students can restore notes or empty them permanently.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('review_notes', 'archived_at')) {
            Schema::table('review_notes', fn (Blueprint $table) => $table->dateTime('archived_at')->nullable());
        }
        if (! Schema::hasColumn('review_notes', 'deleted_at')) {
            Schema::table('review_notes', fn (Blueprint $table) => $table->softDeletes());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('review_notes', 'archived_at')) {
            Schema::table('review_notes', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        }
        if (Schema::hasColumn('review_notes', 'deleted_at')) {
            Schema::table('review_notes', fn (Blueprint $table) => $table->dropColumn('deleted_at'));
        }
    }
};
