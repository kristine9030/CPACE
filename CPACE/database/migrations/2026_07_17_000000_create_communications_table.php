<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some CPACE installations use the imported legacy schema (integer user
        // IDs), while fresh Laravel installs use big integers. These references
        // intentionally remain unconstrained so the feature works with both.
        if (! Schema::hasTable('communications')) {
            Schema::create('communications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('audience', 20);
                $table->string('target_type', 30)->default('all');
                $table->json('target_filters')->nullable();
                $table->string('title');
                $table->text('message');
                $table->string('type', 30)->default('announcement');
                $table->string('priority', 20)->default('normal');
                $table->string('link')->nullable();
                $table->unsignedInteger('recipient_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('notifications', 'communication_id')) {
            Schema::table('notifications', fn (Blueprint $table) => $table->unsignedBigInteger('communication_id')->nullable()->after('id'));
        }
        if (! Schema::hasColumn('notifications', 'sender_id')) {
            Schema::table('notifications', fn (Blueprint $table) => $table->unsignedBigInteger('sender_id')->nullable()->after('recipient_id'));
        }
        if (! Schema::hasColumn('notifications', 'link')) {
            Schema::table('notifications', fn (Blueprint $table) => $table->string('link')->nullable()->after('message'));
        }
        if (! Schema::hasColumn('notifications', 'updated_at')) {
            Schema::table('notifications', fn (Blueprint $table) => $table->timestamp('updated_at')->nullable()->after('created_at'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notifications', 'communication_id')) {
            Schema::table('notifications', fn (Blueprint $table) => $table->dropColumn('communication_id'));
        }
        Schema::dropIfExists('communications');
    }
};
