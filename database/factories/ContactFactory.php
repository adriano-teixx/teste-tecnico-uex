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
        $location = $this->faker->randomElement($this->brazilianCities());

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
            'city' => $location['city'],
            'state' => $location['state'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ];
    }

    public function brazilianCoordinates(): self
    {
        return $this->state(function () {
            $location = $this->faker->randomElement($this->brazilianCities());

            return [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ];
        });
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

    /**
     * A curated list of Brazilian capitals with fixed coordinates.
     */
    protected function brazilianCities(): array
    {
        return [
            ['city' => 'São Paulo', 'state' => 'SP', 'latitude' => -23.55052, 'longitude' => -46.63331],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ', 'latitude' => -22.90685, 'longitude' => -43.17290],
            ['city' => 'Brasília', 'state' => 'DF', 'latitude' => -15.79340, 'longitude' => -47.88230],
            ['city' => 'Salvador', 'state' => 'BA', 'latitude' => -12.97775, 'longitude' => -38.50163],
            ['city' => 'Fortaleza', 'state' => 'CE', 'latitude' => -3.73186, 'longitude' => -38.52667],
            ['city' => 'Belo Horizonte', 'state' => 'MG', 'latitude' => -19.91668, 'longitude' => -43.93449],
            ['city' => 'Curitiba', 'state' => 'PR', 'latitude' => -25.42836, 'longitude' => -49.27325],
            ['city' => 'Recife', 'state' => 'PE', 'latitude' => -8.04756, 'longitude' => -34.87702],
            ['city' => 'Porto Alegre', 'state' => 'RS', 'latitude' => -30.03465, 'longitude' => -51.21766],
            ['city' => 'Manaus', 'state' => 'AM', 'latitude' => -3.11903, 'longitude' => -60.02173],
            ['city' => 'Belém', 'state' => 'PA', 'latitude' => -1.45583, 'longitude' => -48.50389],
            ['city' => 'Goiânia', 'state' => 'GO', 'latitude' => -16.68689, 'longitude' => -49.26479],
            ['city' => 'Florianópolis', 'state' => 'SC', 'latitude' => -27.59487, 'longitude' => -48.54822],
            ['city' => 'Vitória', 'state' => 'ES', 'latitude' => -20.31550, 'longitude' => -40.31278],
            ['city' => 'João Pessoa', 'state' => 'PB', 'latitude' => -7.11950, 'longitude' => -34.84501],
            ['city' => 'Natal', 'state' => 'RN', 'latitude' => -5.79448, 'longitude' => -35.21100],
            ['city' => 'Maceió', 'state' => 'AL', 'latitude' => -9.66599, 'longitude' => -35.73500],
            ['city' => 'Teresina', 'state' => 'PI', 'latitude' => -5.09194, 'longitude' => -42.80336],
            ['city' => 'Campo Grande', 'state' => 'MS', 'latitude' => -20.46971, 'longitude' => -54.62012],
            ['city' => 'Cuiabá', 'state' => 'MT', 'latitude' => -15.59327, 'longitude' => -56.09740],
            ['city' => 'São Luís', 'state' => 'MA', 'latitude' => -2.53911, 'longitude' => -44.28273],
            ['city' => 'Porto Velho', 'state' => 'RO', 'latitude' => -8.76116, 'longitude' => -63.90043],
            ['city' => 'Rio Branco', 'state' => 'AC', 'latitude' => -9.97499, 'longitude' => -67.82430],
            ['city' => 'Boa Vista', 'state' => 'RR', 'latitude' => 2.82351, 'longitude' => -60.67583],
            ['city' => 'Macapá', 'state' => 'AP', 'latitude' => 0.03493, 'longitude' => -51.06939],
            ['city' => 'Aracaju', 'state' => 'SE', 'latitude' => -10.94725, 'longitude' => -37.07308],
            ['city' => 'Palmas', 'state' => 'TO', 'latitude' => -10.24909, 'longitude' => -48.32429],
        ];
    }
}
