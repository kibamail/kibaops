<?php

namespace Database\Factories;

use App\Models\SourceCode\SourceCodeConnection;
use App\Models\SourceCode\SourceCodeRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SourceCode\SourceCodeRepository>
 */
class SourceCodeRepositoryFactory extends Factory
{
    protected $model = SourceCodeRepository::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $repoName = $this->faker->slug(2);
        $orgName = $this->faker->userName();
        $ownerRepo = "{$orgName}/{$repoName}";

        return [
            'source_code_connection_id' => SourceCodeConnection::factory(),
            'external_repository_id' => $this->faker->randomNumber(8),
            'name' => $repoName,
            'owner_repo' => $ownerRepo,
            'description' => $this->faker->sentence(),
            'visibility' => $this->faker->randomElement(['public', 'private']),
            'default_branch' => $this->faker->randomElement(['main', 'master', 'develop']),
            'clone_urls' => [
                'https' => "https://github.com/{$ownerRepo}.git",
                'ssh' => "git@github.com:{$ownerRepo}.git",
                'git' => "git://github.com/{$ownerRepo}.git",
            ],
            'web_url' => "https://github.com/{$ownerRepo}",
            'language' => $this->faker->randomElement(['PHP', 'JavaScript', 'Python', 'Java', 'Go']),
            'topics' => $this->faker->randomElements(['laravel', 'php', 'javascript', 'api', 'web'], 2),
            'archived' => $this->faker->boolean(10), // 10% chance of being archived
            'fork' => $this->faker->boolean(20), // 20% chance of being a fork
            'repository_metadata' => [
                'size' => $this->faker->numberBetween(100, 50000),
                'stargazers_count' => $this->faker->numberBetween(0, 1000),
                'watchers_count' => $this->faker->numberBetween(0, 100),
                'forks_count' => $this->faker->numberBetween(0, 50),
                'open_issues_count' => $this->faker->numberBetween(0, 25),
                'license' => $this->faker->randomElement(['MIT', 'Apache-2.0', 'GPL-3.0', null]),
                'created_at' => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('c'),
                'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('c'),
                'pushed_at' => $this->faker->dateTimeBetween('-1 week', 'now')->format('c'),
            ],
            'webhook_configured' => $this->faker->boolean(70), // 70% chance of having webhook
            'last_activity_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function private(): static
    {
        return $this->state([
            'visibility' => 'private',
        ]);
    }

    public function public(): static
    {
        return $this->state([
            'visibility' => 'public',
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'archived' => true,
        ]);
    }

    public function fork(): static
    {
        return $this->state([
            'fork' => true,
        ]);
    }

    public function withWebhook(): static
    {
        return $this->state([
            'webhook_configured' => true,
        ]);
    }

    public function withoutWebhook(): static
    {
        return $this->state([
            'webhook_configured' => false,
        ]);
    }
}
