<?php

namespace App\Enums;

/**
 * Document classification for privately stored uploads.
 */
enum DocumentType: string
{
    case Certificate = 'certificate';
    case SiwesDocument = 'siwes_document';
    case ProjectDocumentation = 'project_documentation';
    case TrainingEvidence = 'training_evidence';
    case CurriculumVitae = 'curriculum_vitae';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Certificate => 'Certificate',
            self::SiwesDocument => 'SIWES document',
            self::ProjectDocumentation => 'Project documentation',
            self::TrainingEvidence => 'Training evidence',
            self::CurriculumVitae => 'Curriculum vitae',
            self::Other => 'Other',
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
