<?php

namespace Database\Factories;

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
        ];
    }

    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create();

            return [
                'user_id' => $user->id,
                'email' => $user->email,
            ];
        });
    }

    public function forWorkspace(Workspace $workspace): static
    {
        return $this->state(fn (array $attributes) => [
            'workspace_id' => $workspace->id,
        ]);
    }
}
