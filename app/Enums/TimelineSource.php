<?php

namespace App\Enums;

/**
 * What kind of record a timeline entry came from. Used to filter the SIWES
 * timeline without storing duplicated copy of each record.
 */
enum TimelineSource: string
{
    case Activity = 'activity';
    case Project = 'project';
    case Skill = 'skill';
    case Achievement = 'achievement';
    case Reflection = 'reflection';
    case WorkReference = 'work_reference';
    case KnowledgeContribution = 'knowledge_contribution';
    case Certificate = 'certificate';

    public function label(): string
    {
        return match ($this) {
            self::Activity => 'Activity',
            self::Project => 'Project',
            self::Skill => 'Skill',
            self::Achievement => 'Achievement',
            self::Reflection => 'Reflection',
            self::WorkReference => 'Work reference',
            self::KnowledgeContribution => 'Knowledge contribution',
            self::Certificate => 'Certificate',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $source): array => [$source->value => $source->label()])
            ->all();
    }
}
