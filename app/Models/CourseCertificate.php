<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Issued when a member completes every module of a course. Kept separate from
 * the `certificates` table because that one belongs to an approved student
 * profile, and members can finish a course before they are approved.
 *
 * @property int $id
 * @property string $uuid
 * @property string $serial_number
 * @property Carbon $issued_at
 */
#[Fillable([
    'uuid', 'course_id', 'user_id', 'recipient_name', 'serial_number',
    'issued_at', 'document_id',
])]
class CourseCertificate extends Model
{
    /** @use HasFactory<CourseCertificateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $certificate): void {
            $certificate->uuid ??= (string) Str::uuid();
            $certificate->serial_number ??= static::generateSerialNumber();
            $certificate->issued_at ??= now();
        });
    }

    /**
     * Human-readable, sortable, and unique: LIVEPETAL-COURSE-2026-0001.
     */
    public static function generateSerialNumber(): string
    {
        do {
            $serial = sprintf(
                'LIVEPETAL-%s-%s',
                now()->format('Y'),
                str_pad((string) (static::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            );
        } while (static::query()->where('serial_number', $serial)->exists());

        return $serial;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function verifyUrl(): string
    {
        return route('courses.certificates.verify', $this->uuid);
    }
}
