<?php

namespace App\Enums;

/**
 * The shape of a community post. Discussion is the default; problems carry an
 * accepted answer.
 */
enum CommunityPostType: string
{
    case Discussion = 'discussion';
    case Problem = 'problem';
    case Question = 'question';
    case Showcase = 'showcase';

    /**
     * Posts that expect a response and can therefore be marked solved.
     *
     * @return array<int, string>
     */
    public static function answerable(): array
    {
        return [self::Problem->value, self::Question->value];
    }

    public function label(): string
    {
        return match ($this) {
            self::Discussion => 'Discussion',
            self::Problem => 'Problem',
            self::Question => 'Question',
            self::Showcase => 'Showcase',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Discussion => 'Share an update with the intern community.',
            self::Problem => 'Describe something that is blocking you so staff and peers can help.',
            self::Question => 'Ask the community a question.',
            self::Showcase => 'Show something you built or learned.',
        };
    }

    public function isAnswerable(): bool
    {
        return in_array($this->value, self::answerable(), true);
    }
}
