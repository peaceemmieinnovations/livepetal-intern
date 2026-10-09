<?php

namespace App\Models;

use App\Enums\ReportSectionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * The configurable SIWES report structure. Universities differ, so the chapter
 * order and the completeness requirements live here rather than in code.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array<int, string> $sections
 * @property array<string, mixed>|null $requirements
 * @property bool $is_default
 */
#[Fillable(['name', 'slug', 'description', 'sections', 'requirements', 'is_default', 'created_by'])]
class ReportTemplate extends Model
{
    /** @use HasFactory<ReportTemplateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'requirements' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'report_template_id');
    }

    /**
     * Ordered section types as enums.
     *
     * @return Collection<int, ReportSectionType>
     */
    public function sectionTypes(): Collection
    {
        return collect($this->sections ?? [])
            ->map(fn (string $type): ?ReportSectionType => ReportSectionType::tryFrom($type))
            ->filter()
            ->values();
    }

    /**
     * Requirements for a report to be considered ready to finalise.
     *
     * @return array<string, int>
     */
    public function requirements(): array
    {
        return array_merge([
            'profile' => 1,
            'placement' => 1,
            'activities' => 20,
            'weekly_summaries' => 4,
            'projects' => 1,
            'skills' => 3,
            'supervisor_feedback' => 1,
            'documents' => 1,
        ], $this->requirements ?? []);
    }
}
