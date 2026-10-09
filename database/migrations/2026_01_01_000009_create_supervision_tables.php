<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->string('category')->default('general');
            $table->text('comments')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('is_shared_with_student')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'category']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('audience')->default('all');
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->string('status')->default('published');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['audience', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('supervisor_feedback');
    }
};
