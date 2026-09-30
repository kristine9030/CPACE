<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per uploaded external test-run result (Playwright, Postman/
        // Newman, a load benchmark, or a data-warehouse query benchmark) —
        // these suites run offline (CLI/CI), and their JSON/JUnit output is
        // uploaded here so the Super Admin console has one place to review
        // them, per type, over time.
        Schema::create('test_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // frontend | api | load | warehouse
            $table->string('title');
            $table->json('summary')->nullable();
            $table->longText('raw_payload');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_reports');
    }
};
