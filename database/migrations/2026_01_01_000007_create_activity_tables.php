<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable();
            $table->date('activity_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('learned')->nullable();
            $table->text('challenge')->nullable();
            $table->text('resolution')->nullable();
            $table->text('reflection')->nullable();
            $table->json('technologies')->nullable();
            $table->string('external_links')->nullable();
            $table->string('visibility')->default('private');
            $table->string('status')->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'activity_date']);
            $table->index(['status', 'activity_date']);
        });

        Schema::create('weekly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->date('week_starts_on');
            $table->date('week_ends_on');
            $table->text('summary')->nullable();
            $table->text('major_learning')->nullable();
            $table->text('challenges')->nullable();
            $table->text('achievements')->nullable();
            $table->text('next_week_goals')->nullable();
            $table->json('activity_ids')->nullable();
            $table->json('skill_ids')->nullable();
            $table->json('project_ids')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_profile_id', 'week_starts_on'], 'weekly_summaries_unique');
            $table->index('status');
        });

        Schema::create('monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->date('month_starts_on');
            $table->date('month_ends_on');
            $table->text('summary')->nullable();
            $table->text('major_learning')->nullable();
            $table->text('challenges')->nullable();
            $table->text('achievements')->nullable();
            $table->text('student_reflection')->nullable();
            $table->text('supervisor_feedback')->nullable();
            $table->json('activity_ids')->nullable();
            $table->json('skill_ids')->nullable();
            $table->json('project_ids')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_profile_id', 'month_starts_on'], 'monthly_summaries_unique');
        });

        Schema::create('work_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable();
            $table->string('work_title');
            $table->date('worked_on');
            $table->text('short_description')->nullable();
            $table->text('outcome')->nullable();
            $table->string('evidence_link')->nullable();
            $table->string('visibility')->default('supervisor_only');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'worked_on']);
        });

        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->date('reflection_date');
            $table->string('title');
            $table->text('learned')->nullable();
            $table->text('challenge')->nullable();
            $table->text('resolution')->nullable();
            $table->text('improvements')->nullable();
            $table->text('body')->nullable();
            $table->string('visibility')->default('private');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'reflection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reflections');
        Schema::dropIfExists('work_references');
        Schema::dropIfExists('monthly_summaries');
        Schema::dropIfExists('weekly_summaries');
        Schema::dropIfExists('activity_logs');
    }
};
