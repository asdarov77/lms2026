<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $aircraftPath = $this->faker->slug();
        $coursePath = $this->faker->slug();

        return [
            'title' => $this->faker->sentence(3),
            'short_description' => $this->faker->sentence(8),
            'long_description' => $this->faker->paragraph(),
            'path' => $coursePath,
            'hash' => hash('crc32b', $aircraftPath.$coursePath.time()),
            'visible' => true,
            'aircraft_id' => null,
        ];
    }
}
