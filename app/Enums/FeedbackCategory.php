<?php

namespace App\Enums;

/**
 * Dimensions a supervisor may comment on. Ratings support the written comment,
 * they never replace it.
 */
enum FeedbackCategory: string
{
    case TechnicalLearning = 'technical_learning';
    case Initiative = 'initiative';
    case Teamwork = 'teamwork';
    case Communication = 'communication';
    case ProblemSolving = 'problem_solving';
    case Professionalism = 'professionalism';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::TechnicalLearning => 'Technical learning',
            self::Initiative => 'Initiative',
            self::Teamwork => 'Teamwork',
            self::Communication => 'Communication',
            self::ProblemSolving => 'Problem solving',
            self::Professionalism => 'Professionalism',
            self::General => 'General comments',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category): array => [$category->value => $category->label()])
            ->all();
    }
}
