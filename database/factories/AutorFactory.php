<?php

namespace Database\Factories;

use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutorFactory extends Factory
{
    protected $model = Autor::class;

    public function definition(): array
    {
        return [
            'nome'            => fake()->name(),
            'nacionalidade'   => fake()->country(),
            'data_nascimento' => fake()->optional()->date(),
        ];
    }
}
