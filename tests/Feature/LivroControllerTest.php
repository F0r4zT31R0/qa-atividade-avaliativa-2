<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes atualizados após implementação do LivroController pelo professor.
 * O controller agora possui CRUD completo.
 *
 * BUG ENCONTRADO: Livro::$fillable não inclui 'autor_id', 'titulo', 'isbn', 'data_publicacao'
 * — usa campos antigos ('autor', 'editora', 'ano_publicacao').
 * Isso causa falha no store/update via mass assignment.
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

    public function test_create_retorna_view_com_lista_de_autores(): void
    {
        Autor::factory()->count(3)->create();

        $response = $this->get(route('livros.create'));

        $response->assertStatus(200)
            ->assertViewIs('livros.create')
            ->assertViewHas('autores', fn($a) => $a->count() === 3);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_livro_e_redireciona_para_index(): void
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

    public function test_store_nao_valida_campos_obrigatorios(): void
    {
        // BUG: controller não valida campos — permite salvar livro sem título
        $autor = Autor::factory()->create();

        $this->post(route('livros.store'), [
            'titulo'          => '',
            'isbn'            => '978-3-16-148410-0',
            'data_publicacao' => '2020-01-01',
            'autor_id'        => $autor->id,
        ]);

        // Com validação implementada, o livro não deveria ser criado
        // Sem validação, o livro é criado com título vazio (comportamento atual)
        $this->assertDatabaseCount('livros', 1);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_retorna_view_para_livro_existente(): void
    {
        $livro = Livro::factory()->create();

        $response = $this->get(route('livros.show', $livro->id));

        $response->assertStatus(200)
            ->assertViewIs('livros.show')
            ->assertViewHas('livro', fn($l) => $l->id === $livro->id);
    }

    public function test_show_retorna_404_para_livro_inexistente(): void
    {
        $response = $this->get(route('livros.show', 9999));

        $response->assertStatus(404);
    }

    // ─── EDIT ─────────────────────────────────────────────────────────────────

    public function test_edit_retorna_view_para_livro_existente(): void
    {
        $livro = Livro::factory()->create();

        $response = $this->get(route('livros.edit', $livro->id));

        $response->assertStatus(200)
            ->assertViewIs('livros.edit')
            ->assertViewHas('livro', fn($l) => $l->id === $livro->id);
    }

    public function test_edit_retorna_null_para_livro_inexistente(): void
    {
        // BUG: edit usa Livro::find() sem verificar null — não redireciona
        $response = $this->get(route('livros.edit', 9999));

        // Comportamento atual: retorna view com livro null (possível erro na view)
        $response->assertStatus(200);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_livro_e_redireciona(): void
    {
        $livro = Livro::factory()->create();
        $autor = Autor::factory()->create();

        $response = $this->put(route('livros.update', $livro->id), [
            'titulo'          => 'Título Atualizado',
            'isbn'            => '978-0-00-000000-0',
            'data_publicacao' => '2000-01-01',
            'autor_id'        => $autor->id,
        ]);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'id'     => $livro->id,
            'titulo' => 'Título Atualizado',
        ]);
    }

    public function test_update_retorna_404_para_livro_inexistente(): void
    {
        $response = $this->put(route('livros.update', 9999), [
            'titulo' => 'Fantasma',
        ]);

        $response->assertStatus(404);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_exclui_livro_e_redireciona(): void
    {
        $livro = Livro::factory()->create();

        $response = $this->delete(route('livros.destroy', $livro->id));

        $response->assertRedirect(route('livros.index'));
        $this->assertDatabaseMissing('livros', ['id' => $livro->id]);
    }

    public function test_destroy_retorna_404_para_livro_inexistente(): void
    {
        $response = $this->delete(route('livros.destroy', 9999));

        $response->assertStatus(404);
    }
}
