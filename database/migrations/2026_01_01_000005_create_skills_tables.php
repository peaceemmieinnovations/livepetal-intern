<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('General');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
        });

        Schema::create('student_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('level')->default('beginner');
            $table->date('first_practiced_on')->nullable();
            $table->date('last_practiced_on')->nullable();
            $table->text('evidence')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('project_id')->nullable();
            $table->timestamps();

            $table->unique(['student_profile_id', 'skill_id'], 'student_skills_unique');
            $table->index(['skill_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_skills');
        Schema::dropIfExists('skills');
    }
};
