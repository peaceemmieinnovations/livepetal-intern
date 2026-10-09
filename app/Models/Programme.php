<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $department_id
 * @property string $name
 * @property string $slug
 * @property string|null $code
 * @property string|null $level
 * @property int|null $duration_years
 * @property bool $is_active
 * @property-read Department $department
 */
#[Fillable([
    'department_id', 'name', 'slug', 'code', 'level', 'duration_years', 'is_active',
])]
class Programme extends Model
{
    /** @use HasFactory<ProgrammeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'duration_years' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * @param  Builder<Programme>  $query
     * @return Builder<Programme>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
