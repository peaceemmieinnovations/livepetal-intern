<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();

            $table->string('student_id_number')->nullable();
            $table->string('level', 60)->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('location')->nullable();
            $table->text('bio')->nullable();
            $table->text('learning_goals')->nullable();
            $table->json('professional_interests')->nullable();
            $table->json('portfolio_links')->nullable();

            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 40)->nullable();

            $table->boolean('is_public')->default(false);
            $table->boolean('show_bio')->default(true);
            $table->boolean('show_academic')->default(true);
            $table->boolean('show_skills')->default(true);
            $table->boolean('show_projects')->default(true);
            $table->boolean('show_certificates')->default(true);
            $table->boolean('show_links')->default(true);

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'cohort_id']);
            $table->index('is_public');
        });

        Schema::create('supervisor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->string('team')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('office_location')->nullable();
            $table->text('bio')->nullable();
            $table->json('specialisms')->nullable();
            $table->boolean('accepting_reviews')->default(true);
            $table->timestamps();
        });

        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('training_area')->nullable();
            $table->string('unit')->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['supervisor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placements');
        Schema::dropIfExists('supervisor_profiles');
        Schema::dropIfExists('student_profiles');
    }
};
