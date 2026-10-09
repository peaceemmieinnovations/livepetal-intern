<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * A privately stored upload. Files live on a non-public disk and are only ever
 * served through the authorised download route, never by a guessable path.
 *
 * @property int $id
 * @property int $user_id
 * @property DocumentType $type
 * @property string $title
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property Visibility $visibility
 * @property ReviewStatus $review_status
 */
#[Fillable([
    'user_id', 'type', 'title', 'description', 'disk', 'path', 'original_name',
    'mime_type', 'size', 'visibility', 'review_status', 'verified_by', 'verified_at',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'visibility' => Visibility::class,
            'review_status' => ReviewStatus::class,
            'size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function certificate(): BelongsTo
    {
        return $this->hasOne(Certificate::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->hasOne(JobApplication::class);
    }

    /**
     * The stored file contents. Always resolved through the owning document so
     * the caller has already passed an authorization check.
     */
    public function contents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function humanSize(): string
    {
        $bytes = $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $unit === 'B' ? 0 : 1).' '.$unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
