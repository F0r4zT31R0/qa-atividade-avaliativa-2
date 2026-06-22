<?php
namespace Database\Factories;
use App\Models\Livro;
use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;
class LivroFactory extends Factory
{
    protected $model = Livro::class;
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'isbn' => fake()->unique()->isbn13(),
            'data_publicacao' => fake()->date(),
            'autor_id' => Autor::factory(),
        ];
    }
}
