<?php

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\Certificate;
use App\Models\Cohort;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityPostLike;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\CourseModuleCompletion;
use App\Models\Department;
use App\Models\Document;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\KnowledgeResource;
use App\Models\MonthlySummary;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Reflection;
use App\Models\Report;
use App\Models\ReportReview;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use App\Models\ReportVersion;
use App\Models\SavedOpportunity;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\SupervisorFeedback;
use App\Models\SupervisorProfile;
use App\Models\Tag;
use App\Models\User;
use App\Models\WeeklySummary;
use App\Models\WorkReference;

/**
 * Every factory must actually persist. A wrong column name or enum value only
 * shows up when a row is written, so this walks the whole model layer.
 */
it('persists every domain model through its factory', function (string $model) {
    $instance = $model::factory()->create();

    expect($instance->exists)->toBeTrue()
        ->and($model::query()->whereKey($instance->getKey())->exists())->toBeTrue();
})->with([
    User::class,
    Institution::class,
    Department::class,
    Programme::class,
    Cohort::class,
    StudentProfile::class,
    SupervisorProfile::class,
    Placement::class,
    Skill::class,
    StudentSkill::class,
    ActivityLog::class,
    WeeklySummary::class,
    MonthlySummary::class,
    WorkReference::class,
    Reflection::class,
    Project::class,
    Document::class,
    Attachment::class,
    Certificate::class,
    SupervisorFeedback::class,
    Announcement::class,
    BlogCategory::class,
    Tag::class,
    BlogPost::class,
    BlogComment::class,
    Faq::class,
    CommunityPost::class,
    CommunityPostLike::class,
    CommunityComment::class,
    Opportunity::class,
    SavedOpportunity::class,
    JobPosting::class,
    JobApplication::class,
    KnowledgeResource::class,
    ReportTemplate::class,
    Report::class,
    ReportVersion::class,
    ReportSection::class,
    ReportReview::class,
    AuditLog::class,
    Setting::class,
    Course::class,
    CourseModule::class,
    CourseEnrollment::class,
    CourseModuleCompletion::class,
    CourseCertificate::class,
]);
