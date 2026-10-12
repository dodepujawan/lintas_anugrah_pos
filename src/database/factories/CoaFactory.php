<?php

namespace Database\Factories;

use App\Models\Coa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coa>
 */
class CoaFactory extends Factory
{
    protected $model = Coa::class;

    public function definition(): array
    {
        $tipes = ['Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban'];

        return [
            'kode_akun' => $this->faker->unique()->numberBetween(10000, 99999),
            'nama_akun' => $this->faker->word(),
            'tipe' => $this->faker->randomElement($tipes),
            'kategori' => $this->faker->word(),
            'level' => $this->faker->numberBetween(1, 5),
            'parent_id' => null,
            'saldo_normal' => $this->faker->randomElement(['D', 'K']),
            'deskripsi' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
