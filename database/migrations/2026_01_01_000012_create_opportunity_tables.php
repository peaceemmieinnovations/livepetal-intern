<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('organization')->nullable();
            $table->text('description');
            $table->string('type')->default('internship');
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->date('deadline')->nullable();
            $table->string('external_url')->nullable();
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deadline']);
            $table->index('type');
        });

        Schema::create('opportunity_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['opportunity_id', 'skill_id']);
        });

        Schema::create('saved_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['opportunity_id', 'user_id'], 'saved_opportunities_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_opportunities');
        Schema::dropIfExists('opportunity_skill');
        Schema::dropIfExists('opportunities');
    }
};
