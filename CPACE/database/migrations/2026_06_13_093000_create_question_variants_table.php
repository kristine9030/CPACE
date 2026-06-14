<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alternative phrasings of a faculty question, shown to students in place
     * of the original to discourage memorisation. The original question is
     * never modified; a variant is just another way to ask the same thing with
     * the same correct answer. Variants can come from a human (faculty), an AI
     * generator, or be left empty (the rule-based paraphraser fills in).
     */
    public function up(): void
    {
        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('variant_text');
            $table->string('source', 20)->default('faculty'); // faculty | ai | rule
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['question_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_variants');
    }
};
