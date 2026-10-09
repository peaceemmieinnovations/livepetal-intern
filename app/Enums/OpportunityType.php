<?php

namespace App\Enums;

enum OpportunityType: string
{
    case Internship = 'internship';
    case Job = 'job';
    case Freelance = 'freelance';
    case Scholarship = 'scholarship';
    case Hackathon = 'hackathon';
    case Competition = 'competition';
    case TrainingProgramme = 'training_programme';
    case LivepetalOpportunity = 'livepetal_opportunity';

    public function label(): string
    {
        return match ($this) {
            self::Internship => 'Internship',
            self::Job => 'Job',
            self::Freelance => 'Freelance',
            self::Scholarship => 'Scholarship',
            self::Hackathon => 'Hackathon',
            self::Competition => 'Competition',
            self::TrainingProgramme => 'Training programme',
            self::LivepetalOpportunity => 'Livepetal opportunity',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
