<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workspace>
 */
class WorkspaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a shorter company name to fit within the 32-character limit
        // Using words instead of company() to get shorter names
        $name = fake()->words(2, true);
        $name = ucfirst($name);
        
        // Ensure name is within the 32-character limit
        if (strlen($name) > 30) {
            $name = substr($name, 0, 30);
        }
        
        return [
            'name' => $name,
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
