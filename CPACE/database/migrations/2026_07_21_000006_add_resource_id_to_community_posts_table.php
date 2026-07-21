<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a feed post reference a Resource Library upload (e.g. "just uploaded X")
     * instead of duplicating the file as a raw community_post_attachments row.
     */
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->after('subject_id')
                ->constrained('community_resources')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resource_id');
        });
    }
};
