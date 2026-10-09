<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attachable_type' => (new Project)->getMorphClass(),
            'attachable_id' => Project::factory(),
            'disk' => 'local',
            'path' => 'attachments/'.$this->faker->uuid().'.png',
            'original_name' => 'screenshot.png',
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(50_000, 900_000),
            'caption' => null,
            'sort_order' => 0,
        ];
    }

    public function forProject(Project $project): static
    {
        return $this->state(fn (): array => [
            'attachable_type' => $project->getMorphClass(),
            'attachable_id' => $project->id,
        ]);
    }
}
