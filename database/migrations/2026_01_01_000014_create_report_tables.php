<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('sections');
            $table->json('requirements')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('report_template_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->unsignedInteger('current_version')->default(0);
            $table->foreignId('current_version_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'status']);
        });

        Schema::create('report_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status')->default('draft');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->text('generation_notes')->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'version_number']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('current_version_id')
                ->references('id')->on('report_versions')->nullOnDelete();
        });

        Schema::create('report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_version_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('heading');
            $table->string('subheading')->nullable();
            $table->longText('body')->nullable();
            $table->text('placeholder_note')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_version_id', 'sort_order']);
        });

        Schema::create('report_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index('report_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_reviews');
        Schema::dropIfExists('report_sections');
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });
        Schema::dropIfExists('report_versions');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('report_templates');
    }
};
