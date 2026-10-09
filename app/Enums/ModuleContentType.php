<?php

namespace App\Enums;

/**
 * How a course module delivers its content.
 */
enum ModuleContentType: string
{
    case Text = 'text';
    case Video = 'video';
    case Link = 'link';
    case Download = 'download';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Reading',
            self::Video => 'Video',
            self::Link => 'External resource',
            self::Download => 'Download',
        };
    }

    /**
     * Types that need something other than the module body to be usable.
     *
     * @return array<int, string>
     */
    public function requiresUrl(): array
    {
        return [self::Video->value, self::Link->value, self::Download->value];
    }

    public function needsUrl(): bool
    {
        return in_array($this->value, self::requiresUrl(), true);
    }
}
