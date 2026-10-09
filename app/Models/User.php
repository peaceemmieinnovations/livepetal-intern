<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string|null $avatar_path
 * @property string $status
 * @property UserRole $role
 * @property Carbon|null $email_verified_at
 * @property string $email
 * @property string $password
 * @property Carbon|null $onboarding_completed_at
 * @property Carbon|null $applied_at
 * @property string|null $application_note
 * @property string|null $decision_note
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $last_seen_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property-read StudentProfile|null $studentProfile
 * @property-read SupervisorProfile|null $supervisorProfile
 * @property-read Placement|null $placement
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'email', 'password',
    'username', 'avatar_path', 'status', 'role',
    'onboarding_completed_at', 'applied_at', 'application_note', 'decision_note',
    'approved_at', 'approved_by', 'rejected_at', 'rejected_by', 'last_seen_at',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'applied_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function supervisorProfile(): HasOne
    {
        return $this->hasOne(SupervisorProfile::class);
    }

    /**
     * The trainee's own SIWES placement, if one has been assigned.
     */
    public function placement(): HasOne
    {
        return $this->hasOne(Placement::class, 'student_id');
    }

    /**
     * Placements this supervisor is responsible for.
     */
    public function supervisedPlacements(): HasMany
    {
        return $this->hasMany(Placement::class, 'supervisor_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function reports(): HasManyThrough
    {
        return $this->hasManyThrough(
            Report::class,
            StudentProfile::class,
            'user_id',
            'student_profile_id',
        );
    }

    public function activities(): HasManyThrough
    {
        return $this->hasManyThrough(
            ActivityLog::class,
            StudentProfile::class,
            'user_id',
            'student_profile_id',
        );
    }

    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(
            Project::class,
            StudentProfile::class,
            'user_id',
            'student_profile_id',
        );
    }

    public function skills(): HasManyThrough
    {
        return $this->hasManyThrough(
            StudentSkill::class,
            StudentProfile::class,
            'user_id',
            'student_profile_id',
        );
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function communityPostLikes(): HasMany
    {
        return $this->hasMany(CommunityPostLike::class);
    }

    public function savedOpportunities(): HasMany
    {
        return $this->hasMany(SavedOpportunity::class);
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function courseCertificates(): HasMany
    {
        return $this->hasMany(CourseCertificate::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function supervisorFeedbackGiven(): HasMany
    {
        return $this->hasMany(SupervisorFeedback::class, 'supervisor_id');
    }

    /**
     * The student profiles this supervisor is assigned to.
     */
    public function supervisedStudents(): BelongsToMany
    {
        return $this->belongsToMany(
            StudentProfile::class,
            'placements',
            'supervisor_id',
            'student_id',
            'id',
            'user_id',
        )->with(['user', 'institution', 'programme', 'cohort']);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeWithRole(Builder $query, UserRole ...$roles): Builder
    {
        return $query->whereIn('role', array_column($roles, 'value'));
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRole::Student->value);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeSupervisors(Builder $query): Builder
    {
        return $query->where('role', UserRole::Supervisor->value);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdministrators(Builder $query): Builder
    {
        return $query->whereIn('role', UserRole::administrative());
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Self-registered members waiting for an administrator to approve intern
     * access. This is the approval queue.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('role', UserRole::Member->value)
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->orderBy('applied_at');
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->role === $role) {
                return true;
            }
        }

        return false;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::Supervisor;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Anyone who may sign in to the supervision or administration areas.
     */
    public function isStaff(): bool
    {
        return $this->role->isAdministrative() || $this->role === UserRole::Supervisor;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    /**
     * A self-registered member with public access but no intern access yet.
     */
    public function isMember(): bool
    {
        return $this->role === UserRole::Member;
    }

    /**
     * Whether an administrator has approved intern access for this account.
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Whether an administrator turned this membership application down.
     */
    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    public function isAwaitingApproval(): bool
    {
        return $this->role->requiresApproval() && ! $this->isApproved() && ! $this->isRejected();
    }

    /**
     * Whether this account may open private trainee records: reports, placements,
     * documents and activity logs.
     */
    public function hasInternAccess(): bool
    {
        return $this->isApproved() && $this->role->hasInternAccess();
    }

    public function needsOnboarding(): bool
    {
        return $this->isStudent() && ! $this->isOnboardingComplete();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Public portfolio URL, or null when the student keeps a private profile.
     */
    public function portfolioUrl(): ?string
    {
        $profile = $this->studentProfile;

        if (! $profile?->is_public || ! $this->username) {
            return null;
        }

        return route('public.students.show', $this->username);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }

    /**
     * Whether this user is the supervisor responsible for the given student.
     */
    public function supervises(User|StudentProfile $student): bool
    {
        $studentId = $student instanceof StudentProfile ? $student->user_id : $student->id;

        return Placement::query()
            ->where('student_id', $studentId)
            ->where('supervisor_id', $this->id)
            ->exists();
    }

    /**
     * A person may read the private records of the trainees they supervise, and
     * administrators may read any trainee record.
     */
    public function canAccessTraineeRecords(User|StudentProfile $student): bool
    {
        return $this->isStudent()
            ? $student instanceof StudentProfile && $student->user_id === $this->id
            : $this->isAdmin() || $this->isSuperAdmin() || $this->supervises($student);
    }

    /**
     * Trainee profiles this user is allowed to browse, honouring role scope.
     *
     * @return Collection<int, StudentProfile>
     */
    public function accessibleStudentProfiles(): Collection
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return StudentProfile::query()->with('user')->get();
        }

        if ($this->isSupervisor()) {
            return $this->supervisedStudents;
        }

        return $this->studentProfile ? collect([$this->studentProfile->load('user')]) : collect();
    }

    /**
     * The landing page for this user, derived from their role.
     */
    public function dashboardRouteName(): string
    {
        return $this->role->dashboardRoute();
    }

    /**
     * Present the user with a readable role name.
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->role->label());
    }
}
