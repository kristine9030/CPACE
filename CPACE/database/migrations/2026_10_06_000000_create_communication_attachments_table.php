<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Files (documents, images, ...) attached to a Program Chair announcement.
 * communication_id stays unconstrained, like the rest of the communications
 * tables, so it works with both legacy and fresh user-id column types.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('communication_attachments')) {
            return;
        }

        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->index();
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('category', 20)->default('other');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_attachments');
    }
};
