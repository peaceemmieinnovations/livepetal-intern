<?php

namespace App\Enums;

/**
 * Chapters of the SIWES report template. Universities differ, so the ordering
 * and inclusion of sections is configuration rather than hard-coded logic.
 */
enum ReportSectionType: string
{
    case CoverPage = 'cover_page';
    case Certification = 'certification';
    case Dedication = 'dedication';
    case Acknowledgement = 'acknowledgement';
    case TableOfContents = 'table_of_contents';
    case ChapterIntroduction = 'chapter_introduction';
    case ChapterOrganization = 'chapter_organization';
    case ChapterActivities = 'chapter_activities';
    case ChapterProject = 'chapter_project';
    case ChapterConclusion = 'chapter_conclusion';
    case Appendices = 'appendices';

    public function label(): string
    {
        return match ($this) {
            self::CoverPage => 'Cover Page',
            self::Certification => 'Certification / Approval',
            self::Dedication => 'Dedication',
            self::Acknowledgement => 'Acknowledgement',
            self::TableOfContents => 'Table of Contents',
            self::ChapterIntroduction => 'Chapter One — Introduction',
            self::ChapterOrganization => 'Chapter Two — Organization Profile',
            self::ChapterActivities => 'Chapter Three — Training Activities',
            self::ChapterProject => 'Chapter Four — Major Project / Practical Experience',
            self::ChapterConclusion => 'Chapter Five — Summary, Conclusion and Recommendations',
            self::Appendices => 'Appendices',
        };
    }

    public function heading(): string
    {
        return match ($this) {
            self::ChapterIntroduction => 'CHAPTER ONE',
            self::ChapterOrganization => 'CHAPTER TWO',
            self::ChapterActivities => 'CHAPTER THREE',
            self::ChapterProject => 'CHAPTER FOUR',
            self::ChapterConclusion => 'CHAPTER FIVE',
            default => strtoupper($this->label()),
        };
    }

    /**
     * Preliminary pages are numbered in roman numerals and excluded from the
     * generated table of contents body listing.
     */
    public function isPreliminary(): bool
    {
        return in_array($this, [
            self::CoverPage,
            self::Certification,
            self::Dedication,
            self::Acknowledgement,
            self::TableOfContents,
        ], true);
    }

    /**
     * The default SIWES chapter order.
     *
     * @return array<int, self>
     */
    public static function defaults(): array
    {
        return [
            self::CoverPage,
            self::Certification,
            self::Dedication,
            self::Acknowledgement,
            self::TableOfContents,
            self::ChapterIntroduction,
            self::ChapterOrganization,
            self::ChapterActivities,
            self::ChapterProject,
            self::ChapterConclusion,
            self::Appendices,
        ];
    }
}
