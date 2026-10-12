<?php

namespace Database\Factories;

use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jurnal>
 */
class JurnalFactory extends Factory
{
    protected $model = Jurnal::class;

    public function definition(): array
    {
        return [
            'no_jurnal' => $this->faker->unique()->numerify('JNL-###'),
            'tanggal' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'status' => 'Posted',
            'keterangan' => $this->faker->sentence(),
            'total_debit' => $this->faker->numberBetween(0, 100000000),
            'total_kredit' => $this->faker->numberBetween(0, 100000000),
            'referensi' => $this->faker->word(),
            'is_posted' => false,
            'posted_at' => null,
            'created_by' => User::factory(),  // Create User on demand
        ];
    }
}
