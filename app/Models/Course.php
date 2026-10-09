<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\CourseLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A free course. Public: anyone who has registered can start one, and completing
 * it issues a certificate. Enrolment is deliberately open rather than invite-only.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ContentStatus $status
 * @property CourseLevel $level
 */
#[Fillable([
    'title', 'slug', 'summary', 'description', 'level', 'category_id',
    'estimated_minutes', 'status', 'created_by', 'published_at',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'level' => CourseLevel::class,
            'status' => ContentStatus::class,
            'estimated_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $course): void {
            if (blank($course->slug)) {
                $course->slug = static::uniqueSlug($course->title, $course->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'category_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function publishedModules(): HasMany
    {
        return $this->modules()->where('is_published', true);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(CourseCertificate::class);
    }

    public function enrollmentFor(?User $user): ?CourseEnrollment
    {
        if ($user === null) {
            return null;
        }

        return $this->enrollments()->where('user_id', $user->id)->first();
    }

    public function certificateFor(?User $user): ?CourseCertificate
    {
        if ($user === null) {
            return null;
        }

        return $this->certificates()->where('user_id', $user->id)->first();
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at');
    }

    public function moduleCount(): int
    {
        return $this->publishedModules()->count();
    }
}
