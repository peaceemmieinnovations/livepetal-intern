<?php

namespace Database\Factories;

use App\Enums\ReportSectionType;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReportTemplate>
 */
class ReportTemplateFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase().' SIWES Report Template';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(3)),
            'description' => fake()->sentence(14),
            'sections' => array_map(
                fn (ReportSectionType $type): string => $type->value,
                ReportSectionType::cases(),
            ),
            'requirements' => [
                'activities' => 20,
                'weekly_summaries' => 4,
                'projects' => 1,
                'skills' => 3,
            ],
            'is_default' => false,
            'created_by' => User::factory()->admin(),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }
}
