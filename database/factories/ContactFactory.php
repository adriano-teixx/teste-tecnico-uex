<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $state = $this->faker->stateAbbr;
        $city = $this->faker->city;

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name,
            'cpf' => $this->generateValidCpf(),
            'phone' => $this->faker->phoneNumber,
            'cep' => str_pad((string) $this->faker->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'street' => $this->faker->streetName,
            'number' => (string) $this->faker->numberBetween(1, 9999),
            'complement' => $this->faker->optional()->secondaryAddress,
            'district' => $this->faker->citySuffix,
            'city' => $city,
            'state' => strtoupper($state),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
        ];
    }

    /**
     * Generate a valid CPF string.
     */
    protected function generateValidCpf(): string
    {
        $numbers = [];

        for ($i = 0; $i < 9; $i++) {
            $numbers[] = $this->faker->numberBetween(0, 9);
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($c = 0; $c < $t; $c++) {
                $sum += $numbers[$c] * ($t + 1 - $c);
            }

            $digit = (($sum * 10) % 11) % 10;
            $numbers[] = $digit;
        }

        return implode('', $numbers);
    }
}
