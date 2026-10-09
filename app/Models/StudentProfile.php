<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A trainee's record. Holds academic, professional and privacy settings. Data
 * that an administrator verifies (institution, cohort, matric number) is only
 * editable through the supervised onboarding process, never from the profile
 * page alone.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $institution_id
 * @property int|null $department_id
 * @property int|null $programme_id
 * @property int|null $cohort_id
 * @property string|null $student_id_number
 * @property string|null $level
 * @property int|null $graduation_year
 * @property string|null $phone
 * @property string|null $location
 * @property string|null $bio
 * @property string|null $learning_goals
 * @property array<int, string>|null $professional_interests
 * @property array<int, string>|null $portfolio_links
 * @property string|null $github_url
 * @property string|null $linkedin_url
 * @property string|null $website_url
 * @property bool $is_public
 * @property bool $show_bio
 * @property bool $show_academic
 * @property bool $show_skills
 * @property bool $show_projects
 * @property bool $show_certificates
 * @property bool $show_links
 * @property Carbon|null $approved_at
 * @property-read User $user
 * @property-read Institution|null $institution
 * @property-read Department|null $department
 * @property-read Programme|null $programme
 * @property-read Cohort|null $cohort
 * @property-read Placement|null $placement
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id', 'institution_id', 'department_id', 'programme_id', 'cohort_id',
    'student_id_number', 'level', 'graduation_year', 'phone', 'location',
    'bio', 'learning_goals', 'professional_interests', 'portfolio_links',
    'github_url', 'linkedin_url', 'website_url',
    'emergency_contact_name', 'emergency_contact_phone',
    'is_public', 'show_bio', 'show_academic', 'show_skills', 'show_projects',
    'show_certificates', 'show_links', 'approved_at', 'approved_by',
])]
class StudentProfile extends Model
{
    /** @use HasFactory<StudentProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'professional_interests' => 'array',
            'portfolio_links' => 'array',
            'graduation_year' => 'integer',
            'is_public' => 'boolean',
            'show_bio' => 'boolean',
            'show_academic' => 'boolean',
            'show_skills' => 'boolean',
            'show_projects' => 'boolean',
            'show_certificates' => 'boolean',
            'show_links' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function placement(): HasOne
    {
        return $this->hasOne(Placement::class, 'student_id', 'user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function weeklySummaries(): HasMany
    {
        return $this->hasMany(WeeklySummary::class);
    }

    public function monthlySummaries(): HasMany
    {
        return $this->hasMany(MonthlySummary::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(StudentSkill::class);
    }

    public function reflections(): HasMany
    {
        return $this->hasMany(Reflection::class);
    }

    public function workReferences(): HasMany
    {
        return $this->hasMany(WorkReference::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(SupervisorFeedback::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function knowledgeContributions(): HasMany
    {
        return $this->hasMany(KnowledgeResource::class);
    }

    /**
     * @param  Builder<StudentProfile>  $query
     * @return Builder<StudentProfile>
     */
    #[Scope]
    public function approved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * @param  Builder<StudentProfile>  $query
     * @return Builder<StudentProfile>
     */
    #[Scope]
    public function publicProfiles(Builder $query): Builder
    {
        return $query->where('is_public', true)->whereNotNull('approved_at');
    }

    public function supervisor(): ?User
    {
        return $this->placement()->with('supervisor')->first()?->supervisor;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Fraction of the onboarding checklist that is complete, 0-100.
     */
    public function onboardingProgress(): int
    {
        $checks = [
            filled($this->user?->name),
            filled($this->bio),
            $this->user?->avatar_path !== null,
            $this->institution_id !== null,
            $this->department_id !== null,
            $this->programme_id !== null,
            filled($this->student_id_number),
            filled($this->level),
            $this->cohort_id !== null,
            $this->placement()->exists(),
            filled($this->github_url) || filled($this->linkedin_url) || filled($this->website_url),
            ! empty($this->professional_interests),
            $this->is_public || ! $this->is_public,
        ];

        $complete = collect($checks)->filter(fn (bool $check): bool => $check)->count();

        return (int) round($complete / count($checks) * 100);
    }

    /**
     * The human readable name shown across the platform.
     */
    public function displayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->user?->name ?? 'Trainee');
    }
}
