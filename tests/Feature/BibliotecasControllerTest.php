<?php

namespace Tests\Feature;

use App\Models\Biblioteca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes atualizados após correção do BibliotecasController pelo professor.
 * O método edit() agora retorna corretamente a view 'bibliotecas.edit'.
 */
class BibliotecasControllerTest extends TestCase
{
    use RefreshDatabase;

    private function criarBiblioteca(string $nome = 'Biblioteca Teste'): Biblioteca
    {
        $user = User::factory()->create();
        return Biblioteca::create([
            'created_by' => $user->id,
            'nome'       => $nome,
            'endereco'   => 'Rua Teste, 123',
        ]);
    }

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_index_retorna_view_com_bibliotecas(): void
    {
        $this->criarBiblioteca('Biblioteca Central');
        $this->criarBiblioteca('Biblioteca Norte');

        $response = $this->get(route('bibliotecas.index'));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.index')
            ->assertViewHas('bibliotecas', fn($b) => $b->count() === 2);
    }

    public function test_index_filtra_por_nome(): void
    {
        $this->criarBiblioteca('Biblioteca Central');
        $this->criarBiblioteca('Biblioteca Norte');

        $response = $this->get(route('bibliotecas.index', ['nome' => 'Central']));

        $response->assertStatus(200)
            ->assertViewHas('bibliotecas', fn($b) => $b->count() === 1);
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function test_create_retorna_view_de_nova_biblioteca(): void
    {
        $response = $this->get(route('bibliotecas.create'));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.new');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_biblioteca_e_redireciona(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('bibliotecas.store'), [
            'created_by' => $user->id,
            'nome'       => 'Nova Biblioteca',
            'endereco'   => 'Rua Nova, 100',
        ]);

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca criada com sucesso');

        $this->assertDatabaseHas('bibliotecas', ['nome' => 'Nova Biblioteca']);
    }

    // ─── EDIT ─────────────────────────────────────────────────────────────────

    public function test_edit_retorna_view_correta_para_biblioteca_existente(): void
    {
        $biblioteca = $this->criarBiblioteca();

        $response = $this->get(route('bibliotecas.edit', ['id' => $biblioteca->id]));

        // CORREÇÃO: professor corrigiu para retornar 'bibliotecas.edit'
        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.edit')
            ->assertViewHas('biblioteca', fn($b) => $b->id === $biblioteca->id);
    }

    public function test_edit_redireciona_para_index_quando_biblioteca_nao_existe(): void
    {
        $response = $this->get(route('bibliotecas.edit', ['id' => 9999]));

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('error', 'Biblioteca não encontrada');
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_biblioteca_e_redireciona(): void
    {
        $biblioteca = $this->criarBiblioteca();

        $response = $this->put(route('bibliotecas.update', ['id' => $biblioteca->id]), [
            'nome'     => 'Nome Atualizado',
            'endereco' => 'Rua Atualizada, 999',
        ]);

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca atualizada com sucesso');

        $this->assertDatabaseHas('bibliotecas', [
            'id'   => $biblioteca->id,
            'nome' => 'Nome Atualizado',
        ]);
    }

    public function test_update_retorna_404_para_biblioteca_inexistente(): void
    {
        $response = $this->put(route('bibliotecas.update', ['id' => 9999]), [
            'nome' => 'Fantasma',
        ]);

        $response->assertStatus(404);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_exclui_biblioteca_e_redireciona(): void
    {
        $biblioteca = $this->criarBiblioteca();

        $response = $this->delete(route('bibliotecas.destroy', ['id' => $biblioteca->id]));

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca excluída com sucesso');

        $this->assertDatabaseMissing('bibliotecas', ['id' => $biblioteca->id]);
    }

    public function test_destroy_retorna_404_para_biblioteca_inexistente(): void
    {
        $response = $this->delete(route('bibliotecas.destroy', ['id' => 9999]));

        $response->assertStatus(404);
    }
}
