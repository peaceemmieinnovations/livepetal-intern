<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_name
 * @property string|null $location
 * @property string|null $website
 * @property bool $is_active
 */
#[Fillable(['name', 'slug', 'short_name', 'location', 'website', 'is_active'])]
class Institution extends Model
{
    /** @use HasFactory<InstitutionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * @param  Builder<Institution>  $query
     * @return Builder<Institution>
     */
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
