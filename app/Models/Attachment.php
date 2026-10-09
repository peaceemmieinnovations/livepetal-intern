<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Images and screenshots attached to activities, projects and community posts.
 * Attachments live on the public disk because they are visible on public
 * portfolios; anything private is stored as a Document instead.
 *
 * @property int $id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property string|null $caption
 */
#[Fillable([
    'attachable_type', 'attachable_id', 'disk', 'path', 'original_name',
    'mime_type', 'size', 'caption', 'sort_order',
])]
class Attachment extends Model
{
    /** @use HasFactory<AttachmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
