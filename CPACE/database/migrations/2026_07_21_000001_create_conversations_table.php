<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            // 'direct' = 1-on-1 DM, 'group' = a named group chat (including the default community GC).
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name')->nullable();
            // The single auto-membership group every student + alumnus is dropped into.
            $table->boolean('is_default_group')->default(false);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
