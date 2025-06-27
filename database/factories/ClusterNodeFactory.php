<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClusterNode>
 */
class ClusterNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['worker', 'storage']);
        $number = fake()->numberBetween(1, 10);

        return [
            'cluster_id' => \App\Models\Cluster::factory(),
            'node_id' => "test-cluster-{$type}-{$number}",
            'type' => $type,
            'status' => fake()->randomElement(['Healthy', 'Unhealthy', 'Pending']),
            'public_ip' => fake()->optional()->ipv4(),
            'private_ip' => fake()->optional()->localIpv4(),
            'public_ipv6' => fake()->optional()->ipv6(),
            'private_ipv6' => fake()->optional()->ipv6(),
            'server_type' => fake()->randomElement(['cx32', 'cx42', 't3.medium', 't3.large']),
            'cpu_cores' => fake()->numberBetween(2, 16),
            'ram_gb' => fake()->numberBetween(4, 64),
            'disk_gb' => fake()->numberBetween(40, 500),
            'os' => 'ubuntu-24.04',
        ];
    }
}
