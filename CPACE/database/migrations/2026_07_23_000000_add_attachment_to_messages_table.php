<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('body');
            $table->string('original_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('original_name');
            $table->string('file_category', 20)->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'original_name', 'file_size', 'file_category']);
        });
    }
};
