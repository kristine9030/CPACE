<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Alumni Community Resource Library — a persistent, filterable store
     * of study materials uploaded by alumni, decoupled from the chronological
     * community_posts feed so files don't get buried under newer posts.
     */
    public function up(): void
    {
        Schema::create('community_resources', function (Blueprint $table) {
            $table->id();
            // Match the existing column types: users.id is int unsigned,
            // subjects.id is tinyint unsigned (see community_post_likes.user_id /
            // materials.uploaded_by / topics.subject_id's actual DB column).
            $table->unsignedInteger('uploader_id');
            $table->unsignedTinyInteger('subject_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            // Coarse category used to pick an icon: pdf, word, excel, powerpoint, image, video, archive, text, other.
            $table->string('file_category', 20)->default('other');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamps();

            $table->foreign('uploader_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->index(['downloads_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_resources');
    }
};
