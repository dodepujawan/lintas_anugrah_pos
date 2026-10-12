<?php

namespace Database\Factories;

use App\Models\JurnalItemArchive;
use App\Models\Jurnal;
use App\Models\Coa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JurnalItemArchive>
 */
class JurnalItemArchiveFactory extends Factory
{
    protected $model = JurnalItemArchive::class;

    public function definition(): array
    {
        return [
            'jurnal_id' => Jurnal::factory(),
            'coa_id' => Coa::factory(),
            'deskripsi' => $this->faker->sentence(),
            'debit' => $this->faker->randomElement([0, $this->faker->numberBetween(1000, 100000)]),
            'kredit' => $this->faker->randomElement([0, $this->faker->numberBetween(1000, 100000)]),
        ];
    }
}
