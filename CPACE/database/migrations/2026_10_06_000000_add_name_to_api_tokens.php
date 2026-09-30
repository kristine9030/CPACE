<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mobile-app login tokens (AuthApiController) leave `name` null. A
        // named token is one a Super Admin explicitly issued for automation
        // (CI uploading a test report, a scheduled benchmark, ...) via the
        // new API Tokens page — see SuperAdmin\ApiTokenController.
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->string('name')->nullable()->after('user_id');
            $table->timestamp('last_used_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropColumn(['name', 'last_used_at']);
        });
    }
};
