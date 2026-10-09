<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\ReportSectionType;
use App\Models\ActivityLog;
use App\Models\Placement;
use App\Models\Project;
use App\Models\ReportTemplate;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds report section bodies ONLY from records that already exist. Anything
 * the system cannot know is left as a clearly marked prompt for the student,
 * and every section records where its content came from.
 *
 * @phpstan-type Section array{type: ReportSectionType, body: string, placeholder_note: ?string}
 */
class ReportBuilder
{
    /**
     * Build the configured template sections for one trainee.
     *
     * @return array<int, array{type: ReportSectionType, body: string, placeholder_note: ?string}>
     */
    public function sections(User $user, ReportTemplate $template): array
    {
        $profile = $user->studentProfile;
        $placement = $user->placement;

        $logs = $profile?->activities()
            ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])
            ->orderBy('activity_date')
            ->get();

        $projects = $profile?->projects()->orderBy('starts_on')->get();
        $skills = $profile?->skills()->with('skill')->orderBy('id')->get();
        $summaries = $profile?->weeklySummaries()
            ->whereIn('status', [ActivityStatus::Submitted->value, ActivityStatus::Published->value])
            ->orderBy('week_starts_on')
            ->get();

        $sections = [];

        foreach ($template->sectionTypes() as $type) {
            $sections[] = match ($type) {
                ReportSectionType::ChapterIntroduction => $this->introduction($user, $profile, $placement),
                ReportSectionType::ChapterOrganization => $this->organization(),
                ReportSectionType::ChapterActivities => $this->activities($logs),
                ReportSectionType::ChapterProject => $this->projects($projects),
                ReportSectionType::ChapterConclusion => $this->conclusion(),
                ReportSectionType::Appendices => $this->appendices($projects, $skills),
                default => $this->prompt($type),
            };
        }

        return $sections;
    }

    /**
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function introduction(User $user, ?StudentProfile $profile, ?Placement $placement): array
    {
        $text = "This report documents the Students' Industrial Work Experience Scheme (SIWES) training undertaken by {$user->name}";

        if ($profile?->programme) {
            $text .= ", a student of {$profile->programme->name}";
        }
        if ($profile?->department) {
            $text .= " in the Department of {$profile->department->name}";
        }
        if ($profile?->institution) {
            $text .= ", {$profile->institution->name}";
        }
        $text .= '. ';

        if ($placement?->starts_on && $placement?->ends_on) {
            $text .= 'The training ran from '.$placement->starts_on->format('d M Y')
                .' to '.$placement->ends_on->format('d M Y').' at Livepetal Systems Limited';
        } else {
            $text .= 'The training took place at Livepetal Systems Limited';
        }
        if (filled($placement?->training_area)) {
            $text .= ", in the area of {$placement->training_area}";
        }
        $text .= ".\n\n[Add the objectives of SIWES and the importance of industrial training as required by your institution's guidelines.]";

        return [
            'type' => ReportSectionType::ChapterIntroduction,
            'body' => $text,
            'placeholder_note' => null,
        ];
    }

    /**
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function organization(): array
    {
        return [
            'type' => ReportSectionType::ChapterOrganization,
            'body' => '[Write about Livepetal Systems Limited here: background, structure, departments/services and the technology environment you worked in. The system does not hold this information, so it is left for you to complete.]',
            'placeholder_note' => 'Needs your input',
        ];
    }

    /**
     * @param  Collection<int, ActivityLog>|null  $logs
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function activities($logs): array
    {
        if ($logs === null || $logs->isEmpty()) {
            return [
                'type' => ReportSectionType::ChapterActivities,
                'body' => '[No submitted activities were found. Log and submit your daily activities, then regenerate this section.]',
                'placeholder_note' => 'Needs your input',
            ];
        }

        $reviewed = $logs->where('status', ActivityStatus::Published->value)->count();
        $lines = [];
        $lines[] = sprintf(
            '%d activities were recorded between %s and %s; %d of them were reviewed by a supervisor.',
            $logs->count(),
            $logs->first()->activity_date->format('d M Y'),
            $logs->last()->activity_date->format('d M Y'),
            $reviewed,
        );

        foreach ($logs->groupBy(fn ($log): string => $log->activity_date->copy()->startOfWeek()->toDateString()) as $weekStart => $items) {
            $lines[] = '';
            $lines[] = 'Week beginning '.Carbon::parse($weekStart)->format('d M Y');

            foreach ($items as $log) {
                $mark = $log->status === ActivityStatus::Published ? 'reviewed' : 'awaiting review';
                $line = "- {$log->activity_date->format('d M')}: {$log->title} ({$mark}) — {$log->description}";
                if (filled($log->learned)) {
                    $line .= " Learned: {$log->learned}";
                }
                $lines[] = $line;
            }
        }

        return [
            'type' => ReportSectionType::ChapterActivities,
            'body' => implode("\n", $lines),
            'placeholder_note' => 'Generated from your activity log',
        ];
    }

    /**
     * @param  Collection<int, Project>|null  $projects
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function projects($projects): array
    {
        if ($projects === null || $projects->isEmpty()) {
            return [
                'type' => ReportSectionType::ChapterProject,
                'body' => '[No projects were found. Add your projects, then regenerate this section.]',
                'placeholder_note' => 'Needs your input',
            ];
        }

        $blocks = $projects->map(function ($project): string {
            $rows = [sprintf('%s (%s)', $project->name, ucfirst(str_replace('_', ' ', $project->status->value)))];

            foreach ([
                'Role' => $project->role,
                'Technologies' => $project->technologies ? implode(', ', $project->technologies) : null,
                'Description' => $project->description,
                'Problem' => $project->problem,
                'Solution' => $project->solution,
                'Repository' => $project->repository_url,
                'Live link' => $project->live_url,
            ] as $label => $value) {
                if (filled($value)) {
                    $rows[] = "{$label}: {$value}";
                }
            }
            if ($project->starts_on) {
                $rows[] = 'Period: '.$project->starts_on->format('d M Y').($project->ends_on ? ' to '.$project->ends_on->format('d M Y') : ' to present');
            }

            return implode("\n", $rows);
        });

        return [
            'type' => ReportSectionType::ChapterProject,
            'body' => $blocks->implode("\n\n")."\n\n[For your major project, add analysis, design, testing and results in your own words.]",
            'placeholder_note' => 'Generated from your projects',
        ];
    }

    /**
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function conclusion(): array
    {
        return [
            'type' => ReportSectionType::ChapterConclusion,
            'body' => '[Write your own summary, conclusion and recommendations here. These are your judgements, so they are not generated.]',
            'placeholder_note' => 'Needs your input',
        ];
    }

    /**
     * @param  Collection<int, Project>|null  $projects
     * @param  Collection<int, StudentSkill>|null  $skills
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function appendices($projects, $skills = null): array
    {
        $skillLines = $skills && $skills->isNotEmpty()
            ? collect(['Skills practised:'])->concat(
                $skills->map(fn ($entry): string => '- '.($entry->skill?->name ?? $entry->name).' ('.ucfirst(str_replace('_', ' ', $entry->level->value)).', self-assessed)')
            )
            : collect();

        $links = $projects?->flatMap(fn ($project): array => array_filter([
            $project->repository_url ? "{$project->name} repository: {$project->repository_url}" : null,
            $project->live_url ? "{$project->name} live: {$project->live_url}" : null,
        ]));

        $body = collect([$skillLines, $links])
            ->filter(fn ($chunk): bool => $chunk && $chunk->isNotEmpty())
            ->map(fn ($chunk) => $chunk->implode("\n"))
            ->push('[Attach screenshots, certificates and other evidence as your institution requires.]')
            ->implode("\n\n");

        return [
            'type' => ReportSectionType::Appendices,
            'body' => $body,
            'placeholder_note' => null,
        ];
    }

    /**
     * @return array{type: ReportSectionType, body: string, placeholder_note: ?string}
     */
    private function prompt(ReportSectionType $type): array
    {
        return [
            'type' => $type,
            'body' => "[Write the {$type->label()} content here. The system leaves this section for you to complete personally.]",
            'placeholder_note' => 'Needs your input',
        ];
    }
}
