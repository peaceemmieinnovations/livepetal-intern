<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public membership, free courses with certificates, community problem posts and
 * the administrator-controlled application window for internship listings.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Public members register themselves and wait for approval before any
        // intern access, so the queue has to be queryable.
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('applied_at')->nullable()->after('onboarding_completed_at');
            $table->text('application_note')->nullable()->after('applied_at');
            $table->text('decision_note')->nullable()->after('application_note');
        });

        // A problem post is a community post with a question shape and an
        // accepted answer.
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->string('post_type')->default('discussion')->after('project_id');
            $table->boolean('is_solved')->default(false)->after('is_pinned');
            $table->unsignedBigInteger('solved_by')->nullable()->after('is_solved');
            $table->timestamp('solved_at')->nullable()->after('solved_by');
        });

        // Applications stay visible after the deadline; only the window that
        // accepts submissions is switchable by an administrator.
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->boolean('is_accepting_applications')->default(true)->after('deadline');
            $table->timestamp('applications_open_at')->nullable()->after('is_accepting_applications');
            $table->timestamp('applications_close_at')->nullable()->after('applications_open_at');
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('level')->default('beginner');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('skills')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('course_modules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('content_type')->default('text');
            $table->string('external_url')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        Schema::create('course_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedSmallInteger('progress_percent')->default(0);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['course_id', 'user_id']);
        });

        Schema::create('course_module_completions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('course_module_id');
            $table->unsignedBigInteger('course_enrollment_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->foreign('course_module_id')->references('id')->on('course_modules')->cascadeOnDelete();
            $table->foreign('course_enrollment_id')->references('id')->on('course_enrollments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['course_module_id', 'course_enrollment_id'], 'course_module_completions_unique');
        });

        // Kept separate from `certificates`, which belongs to a student profile.
        // A member who is not yet an approved intern can still earn one.
        Schema::create('course_certificates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('user_id');
            $table->string('recipient_name');
            $table->string('serial_number')->unique();
            $table->timestamp('issued_at');
            $table->unsignedBigInteger('document_id')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
            $table->unique(['course_id', 'user_id'], 'course_certificates_course_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_certificates');
        Schema::dropIfExists('course_module_completions');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');

        Schema::table('job_postings', function (Blueprint $table): void {
            $table->dropColumn(['is_accepting_applications', 'applications_open_at', 'applications_close_at']);
        });

        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn(['post_type', 'is_solved', 'solved_by', 'solved_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['applied_at', 'application_note', 'decision_note']);
        });
    }
};
