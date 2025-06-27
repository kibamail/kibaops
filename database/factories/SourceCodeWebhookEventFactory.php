<?php

namespace Database\Factories;

use App\Models\SourceCode\SourceCodeConnection;
use App\Models\SourceCode\SourceCodeRepository;
use App\Models\SourceCode\SourceCodeWebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SourceCode\SourceCodeWebhookEvent>
 */
class SourceCodeWebhookEventFactory extends Factory
{
    protected $model = SourceCodeWebhookEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = $this->faker->randomElement(['push', 'pull_request', 'merge_request', 'release', 'tag']);
        $action = $this->faker->randomElement(['opened', 'closed', 'merged', 'created', 'deleted']);
        $branchName = $this->faker->randomElement(['main', 'master', 'develop', 'feature/test']);
        $commitSha = $this->faker->sha1();

        return [
            'source_code_connection_id' => SourceCodeConnection::factory(),
            'source_code_repository_id' => SourceCodeRepository::factory(),
            'external_event_id' => $this->faker->uuid(),
            'event_type' => $eventType,
            'event_action' => $action,
            'branch_name' => $branchName,
            'commit_sha' => $commitSha,
            'payload' => [
                'action' => $action,
                'ref' => "refs/heads/{$branchName}",
                'head_commit' => [
                    'id' => $commitSha,
                    'message' => $this->faker->sentence(),
                    'author' => [
                        'name' => $this->faker->name(),
                        'email' => $this->faker->email(),
                    ],
                ],
                'repository' => [
                    'id' => $this->faker->randomNumber(8),
                    'name' => $this->faker->slug(2),
                    'full_name' => $this->faker->userName() . '/' . $this->faker->slug(2),
                ],
            ],
            'normalized_payload' => [
                'event_type' => $eventType,
                'action' => $action,
                'branch' => $branchName,
                'commit_sha' => $commitSha,
                'author' => $this->faker->name(),
                'message' => $this->faker->sentence(),
            ],
            'processing_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'processing_attempts' => $this->faker->numberBetween(0, 3),
            'processed_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'processing_status' => 'pending',
            'processing_attempts' => 0,
            'processed_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state([
            'processing_status' => 'processing',
            'processing_attempts' => 1,
            'processed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'processing_status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'processing_status' => 'failed',
            'processing_attempts' => 3,
            'processed_at' => null,
        ]);
    }

    public function pushEvent(): static
    {
        return $this->state([
            'event_type' => 'push',
            'event_action' => 'created',
        ]);
    }

    public function pullRequestEvent(): static
    {
        return $this->state([
            'event_type' => 'pull_request',
            'event_action' => 'opened',
        ]);
    }
}
