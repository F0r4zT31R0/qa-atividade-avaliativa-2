<?php

namespace Tests\Feature;

use App\Models\Autor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes atualizados após correção do AutorController pelo professor.
 * O método edit() agora retorna corretamente a view 'autores.edit'.
 *
 * BUG REMANESCENTE: Autor::$fillable ainda contém 'sobrenome',
 * campo que não existe na tabela autores do banco de dados.
 */
class AutorControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_index_retorna_view_com_autores(): void
    {
        Autor::factory()->count(3)->create();

        $response = $this->get(route('autores.index'));

        $response->assertStatus(200)
            ->assertViewIs('autores.index')
            ->assertViewHas('autores', fn($a) => $a->count() === 3);
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function test_create_retorna_view_de_novo_autor(): void
    {
        $response = $this->get(route('autores.create'));

        $response->assertStatus(200)
            ->assertViewIs('autores.create');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_autor_e_redireciona(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome'          => 'Machado de Assis',
            'nacionalidade' => 'Brasileiro',
        ]);

        $response->assertRedirect(route('autores.index'))
            ->assertSessionHas('success', 'Autor criado com sucesso.');

        $this->assertDatabaseHas('autores', ['nome' => 'Machado de Assis']);
    }

    public function test_store_falha_sem_nome(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome'          => '',
            'nacionalidade' => 'Brasileiro',
        ]);

        $response->assertSessionHasErrors('nome');
        $this->assertDatabaseCount('autores', 0);
    }

    public function test_store_falha_com_nome_muito_longo(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome' => str_repeat('A', 201),
        ]);

        $response->assertSessionHasErrors('nome');
        $this->assertDatabaseCount('autores', 0);
    }

    public function test_store_aceita_data_nascimento_valida(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome'            => 'Autor com Data',
            'data_nascimento' => '1990-05-15',
        ]);

        $response->assertRedirect(route('autores.index'));
        $this->assertDatabaseHas('autores', ['nome' => 'Autor com Data']);
    }

    public function test_store_rejeita_data_nascimento_invalida(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome'            => 'Autor Inválido',
            'data_nascimento' => 'nao-e-data',
        ]);

        $response->assertSessionHasErrors('data_nascimento');
        $this->assertDatabaseMissing('autores', ['nome' => 'Autor Inválido']);
    }

    public function test_store_aceita_campos_opcionais_nulos(): void
    {
        $response = $this->post(route('autores.store'), [
            'nome' => 'Somente Nome',
        ]);

        $response->assertRedirect(route('autores.index'));
        $this->assertDatabaseHas('autores', ['nome' => 'Somente Nome']);
    }

    // ─── EDIT ─────────────────────────────────────────────────────────────────

    public function test_edit_retorna_view_correta_para_autor_existente(): void
    {
        $autor = Autor::factory()->create();

        $response = $this->get(route('autores.edit', $autor->id));

        // CORREÇÃO: professor corrigiu para retornar 'autores.edit'
        $response->assertStatus(200)
            ->assertViewIs('autores.edit')
            ->assertViewHas('autor', fn($a) => $a->id === $autor->id);
    }

    public function test_edit_retorna_404_para_autor_inexistente(): void
    {
        $response = $this->get(route('autores.edit', 9999));

        $response->assertStatus(404);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_autor_e_redireciona(): void
    {
        $autor = Autor::factory()->create();

        $response = $this->put(route('autores.update', $autor->id), [
            'nome'          => 'Nome Atualizado',
            'nacionalidade' => 'Portuguesa',
        ]);

        $response->assertRedirect(route('autores.index'))
            ->assertSessionHas('success', 'Autor atualizado com sucesso.');

        $this->assertDatabaseHas('autores', [
            'id'            => $autor->id,
            'nome'          => 'Nome Atualizado',
            'nacionalidade' => 'Portuguesa',
        ]);
    }

    public function test_update_falha_sem_nome(): void
    {
        $autor = Autor::factory()->create();

        $response = $this->put(route('autores.update', $autor->id), [
            'nome' => '',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    public function test_update_retorna_404_para_autor_inexistente(): void
    {
        $response = $this->put(route('autores.update', 9999), [
            'nome' => 'Fantasma',
        ]);

        $response->assertStatus(404);
    }
}
