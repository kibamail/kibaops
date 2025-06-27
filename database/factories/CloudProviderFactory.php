<?php

namespace Database\Factories;

use App\Enums\CloudProviderType;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CloudProvider>
 */
class CloudProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => substr($this->faker->company(), 0, 32),
            'type' => $this->faker->randomElement(CloudProviderType::implemented()),
            'workspace_id' => Workspace::factory(),
        ];
    }

    public function hetzner(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CloudProviderType::HETZNER,
        ]);
    }

    public function digitalOcean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CloudProviderType::DIGITAL_OCEAN,
        ]);
    }
}
