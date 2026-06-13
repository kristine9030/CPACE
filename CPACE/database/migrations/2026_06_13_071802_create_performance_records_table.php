<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->decimal('accuracy_rate', 5, 2)->default(0);
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_records');
    }
};
