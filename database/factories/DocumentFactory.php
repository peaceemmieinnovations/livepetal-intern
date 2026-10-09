<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\ReviewStatus;
use App\Enums\Visibility;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A private stored file. Uploads live on the private disk; nothing here is ever
 * publicly addressable.
 *
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'type' => fake()->randomElement(DocumentType::cases())->value,
            'title' => fake()->sentence(3),
            'description' => null,
            'disk' => 'local',
            'path' => 'documents/'.$this->faker->uuid().'.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(20_000, 4_000_000),
            'visibility' => Visibility::Private->value,
            'review_status' => ReviewStatus::Pending->value,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'review_status' => ReviewStatus::Verified->value,
            'verified_at' => now(),
        ]);
    }

    public function type(DocumentType|string $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type instanceof DocumentType ? $type->value : $type,
        ]);
    }
}
