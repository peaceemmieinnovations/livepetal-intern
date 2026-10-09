<?php

namespace App\Models;

use App\Enums\ReportSectionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One chapter of a generated report version.
 *
 * placeholder_note is filled in when the system could not ground a section in
 * stored records. It is rendered as a visible prompt telling the student what
 * they must supply, never as invented content.
 *
 * @property int $id
 * @property int $report_version_id
 * @property ReportSectionType $type
 * @property string $heading
 * @property string|null $subheading
 * @property string|null $body
 * @property string|null $placeholder_note
 * @property bool $is_edited
 * @property int $sort_order
 */
#[Fillable([
    'report_version_id', 'type', 'heading', 'subheading', 'body', 'placeholder_note',
    'is_edited', 'edited_by', 'edited_at', 'sort_order',
])]
class ReportSection extends Model
{
    /** @use HasFactory<ReportSectionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ReportSectionType::class,
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReportVersion::class, 'report_version_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function wordCount(): int
    {
        return str_word_count(strip_tags((string) $this->body));
    }

    /**
     * True when the section still needs the student to supply real content.
     */
    public function needsInput(): bool
    {
        return filled($this->placeholder_note);
    }
}
