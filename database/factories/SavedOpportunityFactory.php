<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\SavedOpportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedOpportunity>
 */
class SavedOpportunityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'user_id' => User::factory()->student(),
        ];
    }
}
