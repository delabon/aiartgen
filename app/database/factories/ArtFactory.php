<?php

namespace Database\Factories;

use App\Models\User;
use Bluemmb\Faker\PicsumPhotosProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Art>
 */
class ArtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->addProvider(new PicsumPhotosProvider($this->faker));
        $image = $this->faker->image(storage_path(env('APP_ART_GEN_DIR')), 1024, 1024);

        return [
            'filename' => basename($image),
            'title' => $this->faker->sentence(),
            'user_id' => User::factory()
        ];
    }
}
