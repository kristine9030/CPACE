<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * File attached to a `community_posts` row (PDF/Word/notes/computations
     * shared by alumni). `community_posts.id` is int unsigned, matching here.
     */
    public function up(): void
    {
        Schema::create('community_post_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('community_post_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size')->nullable();
            // Coarse category used to pick an icon: pdf, word, excel, powerpoint, image, video, archive, text, other.
            $table->string('file_category', 20)->default('other');
            $table->timestamps();

            $table->foreign('community_post_id')->references('id')->on('community_posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_attachments');
    }
};
