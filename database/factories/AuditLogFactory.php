<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'actor_id' => User::factory()->admin(),
            'action' => 'report.finalized',
            'description' => 'Finalised SIWES report for a cohort.',
            'target_type' => null,
            'target_id' => null,
            'metadata' => ['cohort' => '2026 SIWES Cohort'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }

    public function forActor(?User $actor): static
    {
        return $this->state(fn (): array => ['actor_id' => $actor?->id]);
    }
}
