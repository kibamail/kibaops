<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cluster>
 */
class ClusterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'status' => fake()->randomElement(['Healthy', 'Unhealthy', 'Pending']),
            'workspace_id' => \App\Models\Workspace::factory(),
            'cloud_provider_id' => \App\Models\CloudProvider::factory(),
            'region' => fake()->randomElement(['us-east-1', 'us-west-2', 'eu-west-1']),
            'shared_storage_worker_nodes' => fake()->boolean(20),
        ];
    }
}
