<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            // Match the existing column types: topics.id is smallint, users.id is int.
            $table->unsignedSmallInteger('topic_id');
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            // 'file' for an uploaded document, 'link' for an external URL.
            $table->enum('kind', ['file', 'link'])->default('file');
            // Coarse category used to pick an icon: pdf, word, excel, powerpoint, image, video, archive, link, other.
            $table->string('file_category', 20)->default('other');
            $table->string('file_path')->nullable();      // relative path on the public disk
            $table->string('original_name')->nullable();  // original uploaded filename
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['topic_id', 'is_active']);
            $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
