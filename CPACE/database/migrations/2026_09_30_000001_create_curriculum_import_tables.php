<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staging for importing a PRC Table of Specifications PDF into a curriculum:
 * the parsed outline is held here for the chair to review and edit, and only
 * becomes real topics when committed (same pattern as question imports).
 *
 * topics.tos_weight / tos_items keep the TOS's own "Weight %" and "No. of
 * Items" for each imported topic, shown on the Subject & Curriculum page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('curriculum_import_batches')) {
            Schema::create('curriculum_import_batches', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('curriculum_version_id');
                $t->unsignedInteger('created_by')->nullable();
                $t->string('original_filename');
                $t->string('status', 20)->default('pending'); // pending | committed
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('curriculum_import_items')) {
            Schema::create('curriculum_import_items', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('batch_id');
                $t->unsignedTinyInteger('subject_id');
                $t->unsignedInteger('parent_item_id')->nullable();
                $t->string('ref', 30)->nullable();
                $t->string('name', 150);
                $t->text('full_name')->nullable();
                $t->unsignedTinyInteger('depth')->default(0);
                $t->decimal('weight_percent', 5, 2)->nullable();
                $t->unsignedSmallInteger('item_count')->nullable();
                $t->unsignedSmallInteger('sort_order')->default(0);
                $t->boolean('included')->default(true);
                $t->index('batch_id');
            });
        }

        if (Schema::hasTable('topics')) {
            if (! Schema::hasColumn('topics', 'tos_weight')) {
                Schema::table('topics', fn (Blueprint $t) => $t->decimal('tos_weight', 5, 2)->nullable()->after('sort_order'));
            }
            if (! Schema::hasColumn('topics', 'tos_items')) {
                Schema::table('topics', fn (Blueprint $t) => $t->unsignedSmallInteger('tos_items')->nullable()->after('tos_weight'));
            }
        }
    }

    public function down(): void
    {
        foreach (['tos_items', 'tos_weight'] as $column) {
            if (Schema::hasColumn('topics', $column)) {
                Schema::table('topics', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
        Schema::dropIfExists('curriculum_import_items');
        Schema::dropIfExists('curriculum_import_batches');
    }
};
