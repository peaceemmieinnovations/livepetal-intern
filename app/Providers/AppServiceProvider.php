<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Document;
use App\Models\KnowledgeResource;
use App\Models\MonthlySummary;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\Project;
use App\Models\Reflection;
use App\Models\Report;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\WeeklySummary;
use App\Policies\ActivityLogPolicy;
use App\Policies\BlogCommentPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\CommunityCommentPolicy;
use App\Policies\CommunityPostPolicy;
use App\Policies\CourseEnrollmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\KnowledgeResourcePolicy;
use App\Policies\MonthlySummaryPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PlacementPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReflectionPolicy;
use App\Policies\ReportPolicy;
use App\Policies\StudentProfilePolicy;
use App\Policies\WeeklySummaryPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
    }

    /**
     * Register policy resolution and the role gates consumed by future student,
     * supervisor and administration areas.
     */
    protected function configureAuthorization(): void
    {
        Gate::policy(StudentProfile::class, StudentProfilePolicy::class);
        Gate::policy(Placement::class, PlacementPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(WeeklySummary::class, WeeklySummaryPolicy::class);
        Gate::policy(MonthlySummary::class, MonthlySummaryPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Reflection::class, ReflectionPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(CourseEnrollment::class, CourseEnrollmentPolicy::class);
        Gate::policy(CommunityPost::class, CommunityPostPolicy::class);
        Gate::policy(CommunityComment::class, CommunityCommentPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(BlogComment::class, BlogCommentPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
        Gate::policy(KnowledgeResource::class, KnowledgeResourcePolicy::class);

        Gate::define('manage-platform-content', fn (User $user): bool => $user->isStaff());
        Gate::define('review-trainee-records', fn (User $user): bool => $user->isStaff());
        Gate::define('approve-members', fn (User $user): bool => $user->isAdmin() || $user->isSuperAdmin());
        Gate::define('assign-placements', fn (User $user): bool => $user->isAdmin() || $user->isSuperAdmin());
        Gate::define('moderate-community', fn (User $user): bool => $user->isStaff());
        Gate::define('post-community', fn (?User $user): bool => $user !== null);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
