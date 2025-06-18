<?php

namespace Database\Factories;

use App\Enums\SourceCodeProviderType;
use App\Models\SourceCode\SourceCodeConnection;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SourceCode\SourceCodeConnection>
 */
class SourceCodeConnectionFactory extends Factory
{
    protected $model = SourceCodeConnection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerType = $this->faker->randomElement(SourceCodeProviderType::cases());
        $accountName = $this->faker->userName();

        return [
            'workspace_id' => Workspace::factory(),
            'provider_type' => $providerType,
            'connection_name' => $this->faker->company() . ' ' . $providerType->label(),
            'external_account_id' => $this->faker->randomNumber(8),
            'external_account_name' => $accountName,
            'external_account_type' => $this->faker->randomElement(['user', 'organization']),
            'avatar_url' => $this->faker->imageUrl(200, 200),
            'permissions_scope' => ['read', 'write'],
            'vault_credentials_path' => "source-code/{$providerType->value}/" . $this->faker->uuid(),
            'connection_status' => 'active',
            'last_sync_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'metadata' => [
                'installation_id' => $this->faker->randomNumber(8),
                'account_type' => $this->faker->randomElement(['User', 'Organization']),
            ],
        ];
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_type' => SourceCodeProviderType::GITHUB,
            'vault_credentials_path' => 'source-code/github/' . $this->faker->uuid(),
        ]);
    }

    public function bitbucket(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_type' => SourceCodeProviderType::BITBUCKET,
            'vault_credentials_path' => 'source-code/bitbucket/' . $this->faker->uuid(),
        ]);
    }

    public function gitlab(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_type' => SourceCodeProviderType::GITLAB,
            'vault_credentials_path' => 'source-code/gitlab/' . $this->faker->uuid(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'connection_status' => 'expired',
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'connection_status' => 'revoked',
        ]);
    }
}
