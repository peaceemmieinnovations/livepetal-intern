<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Institution;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'username' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Student->value,
            'status' => User::STATUS_ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Student->value,
            'approved_at' => now(),
        ]);
    }

    /**
     * A self-registered member: public access, no intern access yet.
     */
    public function member(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Member->value,
            'approved_at' => null,
            'applied_at' => now(),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->member()->state(fn (array $attributes): array => [
            'applied_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'application_note' => 'I want to join the Livepetal SIWES programme.',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'approved_at' => now(),
        ]);
    }

    /**
     * A member whose application was turned down. Leaves the approval queue but
     * keeps their public member access.
     */
    public function rejected(?string $reason = null): static
    {
        return $this->member()->state(fn (array $attributes): array => [
            'rejected_at' => now(),
            'decision_note' => $reason ?? 'Not selected for the current window.',
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Supervisor->value,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Admin->value,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::SuperAdmin->value,
        ]);
    }

    /**
     * A student with a profile already created.
     */
    public function withStudentProfile(array $attributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($attributes): void {
            if ($user->studentProfile()->exists()) {
                return;
            }

            StudentProfile::factory()->create(array_merge([
                'user_id' => $user->id,
                'institution_id' => Institution::factory(),
            ], $attributes));
        });
    }

    /**
     * A supervisor with a profile already created.
     */
    public function withSupervisorProfile(array $attributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($attributes): void {
            if ($user->supervisorProfile()->exists()) {
                return;
            }

            SupervisorProfile::factory()->create(array_merge([
                'user_id' => $user->id,
            ], $attributes));
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * A user who has finished the onboarding wizard.
     */
    public function onboarded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'onboarding_completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static {}
}
