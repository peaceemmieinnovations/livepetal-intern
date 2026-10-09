<?php

namespace Database\Factories;

use App\Enums\ReportSectionType;
use App\Models\ReportSection;
use App\Models\ReportVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportSection>
 */
class ReportSectionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(ReportSectionType::cases());

        return [
            'report_version_id' => ReportVersion::factory(),
            'type' => $type->value,
            'heading' => $type->heading(),
            'subheading' => null,
            'body' => fake()->paragraphs(3, true),
            'placeholder_note' => null,
            'is_edited' => false,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function placeholder(): static
    {
        return $this->state(fn (): array => [
            'body' => null,
            'placeholder_note' => 'You have not logged any activity in this period yet. Add entries to your activity log, then regenerate this section.',
        ]);
    }

    public function type(ReportSectionType $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type->value,
            'heading' => $type->heading(),
        ]);
    }
}
