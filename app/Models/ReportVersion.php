<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An immutable snapshot of generated report content. Regenerating the draft
 * creates a new version so previously approved work is never destroyed.
 *
 * @property int $id
 * @property int $report_id
 * @property int $version_number
 * @property ReportStatus $status
 * @property array<string, mixed>|null $source_snapshot
 */
#[Fillable([
    'report_id', 'version_number', 'status', 'generated_by', 'generated_at',
    'edited_by', 'edited_at', 'source_snapshot', 'generation_notes',
])]
class ReportVersion extends Model
{
    /** @use HasFactory<ReportVersionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'version_number' => 'integer',
            'source_snapshot' => 'array',
            'generated_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportSection::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ReportReview::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function label(): string
    {
        return match ($this->status) {
            ReportStatus::Draft => "Draft {$this->version_number}",
            ReportStatus::Submitted => "Submitted v{$this->version_number}",
            ReportStatus::UnderReview => "Under review v{$this->version_number}",
            ReportStatus::RevisionRequested => "Revision requested v{$this->version_number}",
            ReportStatus::Approved => "Approved v{$this->version_number}",
            ReportStatus::Final => "Final v{$this->version_number}",
        };
    }

    public function wordCount(): int
    {
        return $this->sections->sum(fn (ReportSection $section): int => $section->wordCount());
    }

    public function isLocked(): bool
    {
        return $this->status->isOfficial();
    }
}
