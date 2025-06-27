<?php

namespace Database\Factories;

use App\Enums\WorkspaceMembershipRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkspaceMembership>
 */
class WorkspaceMembershipFactory extends Factory
{
    public function definition(): array
    {
        $email = $this->faker->unique()->safeEmail();

        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => null,
            'email' => $email,
            'role' => $this->faker->randomElement(WorkspaceMembershipRole::cases()),
        ];
    }

    public function withUser(): static
    {
        return $this->state(function () {
            $user = User::factory()->create();

            return [
                'user_id' => $user->id,
                'email' => $user->email,
            ];
        });
    }

    public function forWorkspace(Workspace $workspace): static
    {
        return $this->state(fn () => [
            'workspace_id' => $workspace->id,
        ]);
    }

    public function developer(): static
    {
        return $this->state(fn () => [
            'role' => WorkspaceMembershipRole::DEVELOPER,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => WorkspaceMembershipRole::ADMIN,
        ]);
    }
}
