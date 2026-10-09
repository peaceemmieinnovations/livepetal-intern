<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(UserRole::Student->value)->after('email');
            $table->string('username', 60)->nullable()->unique()->after('name');
            $table->string('avatar_path')->nullable()->after('username');
            $table->string('status')->default('active')->after('avatar_path');
            $table->timestamp('onboarding_completed_at')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('onboarding_completed_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable()->after('approved_by');

            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['role', 'status']);
            $table->dropColumn([
                'role', 'username', 'avatar_path', 'status',
                'onboarding_completed_at', 'approved_at', 'approved_by', 'last_seen_at',
            ]);
        });
    }
};
