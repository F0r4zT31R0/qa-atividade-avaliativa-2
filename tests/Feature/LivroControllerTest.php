<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ATENÇÃO: LivroController está completamente vazio.
 * Estes testes documentam o comportamento ESPERADO.
 * Todos irão FALHAR até que o controller seja implementado.
 */
class LivroControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_index_retorna_view_com_livros(): void
    {
        Livro::factory()->count(2)->create();

        $response = $this->get(route('livros.index'));

        $response->assertStatus(200)
            ->assertViewIs('livros.index')
            ->assertViewHas('livros', fn($l) => $l->count() === 2);
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function test_create_retorna_view_de_novo_livro(): void
    {
        $response = $this->get(route('livros.create'));

        $response->assertStatus(200)
            ->assertViewIs('livros.create');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_livro_e_redireciona(): void
    {
        $autor = Autor::factory()->create();

        $response = $this->post(route('livros.store'), [
            'titulo'          => 'Dom Casmurro',
            'isbn'            => '978-3-16-148410-0',
            'data_publicacao' => '1899-01-01',
            'autor_id'        => $autor->id,
        ]);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'titulo'   => 'Dom Casmurro',
            'isbn'     => '978-3-16-148410-0',
            'autor_id' => $autor->id,
        ]);
    }

    public function test_store_nao_persiste_livro_sem_titulo(): void
    {
        $autor = Autor::factory()->create();

        $this->post(route('livros.store'), [
            'titulo'          => '',
            'isbn'            => '978-3-16-148410-0',
            'data_publicacao' => '1899-01-01',
            'autor_id'        => $autor->id,
        ]);

        $this->assertDatabaseCount('livros', 0);
    }

    public function test_store_nao_persiste_livro_sem_autor_valido(): void
    {
        $this->post(route('livros.store'), [
            'titulo'          => 'Livro Sem Autor',
            'isbn'            => '978-3-16-148410-0',
            'data_publicacao' => '2020-01-01',
            'autor_id'        => 9999,
        ]);

        $this->assertDatabaseCount('livros', 0);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_retorna_view_para_livro_existente(): void
    {
        $livro = Livro::factory()->create();

        $response = $this->get(route('livros.show', $livro->id));

        $response->assertStatus(200);
    }

    public function test_show_retorna_404_para_livro_inexistente(): void
    {
        $response = $this->get(route('livros.show', 9999));

        $response->assertStatus(404);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_livro(): void
    {
        $livro = Livro::factory()->create();
        $autor = Autor::factory()->create();

        $response = $this->put(route('livros.update', $livro->id), [
            'titulo'          => 'Título Atualizado',
            'isbn'            => $livro->isbn,
            'data_publicacao' => $livro->data_publicacao,
            'autor_id'        => $autor->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('livros', [
            'id'     => $livro->id,
            'titulo' => 'Título Atualizado',
        ]);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_exclui_livro(): void
    {
        $livro = Livro::factory()->create();

        $response = $this->delete(route('livros.destroy', $livro->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('livros', ['id' => $livro->id]);
    }
}
