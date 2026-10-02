<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A picture and/or a table can be attached to a question (accounting problems
 * need journals, schedules and statements). Mock exam items and class quiz
 * items are snapshots of a question, so they carry the same two columns.
 */
return new class extends Migration
{
    private const TABLES = ['questions', 'mock_exam_items', 'faculty_quiz_items'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            if (! Schema::hasTable($name) || Schema::hasColumn($name, 'image_path')) {
                continue;
            }
            Schema::table($name, function (Blueprint $table) {
                $table->string('image_path')->nullable();
                $table->json('table_data')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'image_path')) {
                Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['image_path', 'table_data']));
            }
        }
    }
};
