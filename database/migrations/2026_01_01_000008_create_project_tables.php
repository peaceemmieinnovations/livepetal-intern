<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->string('role')->nullable();
            $table->json('technologies')->nullable();
            $table->json('documentation')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status')->default('planned');
            $table->string('repository_url')->nullable();
            $table->string('live_url')->nullable();
            $table->string('visibility')->default('private');
            $table->string('review_status')->default('unreviewed');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verifier_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_profile_id', 'slug']);
            $table->index(['status', 'review_status']);
        });

        Schema::create('project_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });

        Schema::table('student_skills', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::table('work_references', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::table('reflections', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reflections', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('work_references', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('student_skills', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('projects');
    }
};
