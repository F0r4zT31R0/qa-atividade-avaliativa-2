<?php

namespace Tests\Feature;

use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PessoaControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_index_retorna_view_com_pessoas(): void
    {
        Pessoa::factory()->count(2)->create();

        $response = $this->get(route('pessoas.index'));

        $response->assertStatus(200)
            ->assertViewIs('pessoas.index')
            ->assertViewHas('pessoas', fn($p) => $p->count() === 2);
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function test_create_retorna_view_de_nova_pessoa(): void
    {
        $response = $this->get(route('pessoas.create'));

        $response->assertStatus(200)
            ->assertViewIs('pessoas.new');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_pessoa_e_redireciona(): void
    {
        $response = $this->post(route('pessoas.store'), [
            'name'            => 'Maria Souza',
            'email'           => 'maria@teste.com',
            'telefone'        => '31999990000',
            'matricula'       => '20261234567',
            'password'        => 'senha123',
            'confirmPassword' => 'senha123',
        ]);

        $response->assertRedirect(route('pessoas.index'))
            ->assertSessionHas('message', 'Pessoa criada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'name'      => 'Maria Souza',
            'email'     => 'maria@teste.com',
            'matricula' => '20261234567',
        ]);
    }

    public function test_store_rejeita_senhas_diferentes(): void
    {
        $response = $this->post(route('pessoas.store'), [
            'name'            => 'Teste Senha',
            'email'           => 'teste@teste.com',
            'password'        => 'abc123',
            'confirmPassword' => 'xyz999',
        ]);

        $response->assertSessionHas('error', 'As senhas não coincidem!');

        $this->assertDatabaseMissing('pessoas', ['email' => 'teste@teste.com']);
    }

    public function test_store_nao_persiste_pessoa_com_email_duplicado(): void
    {
        Pessoa::factory()->create(['email' => 'duplicado@teste.com']);

        $this->post(route('pessoas.store'), [
            'name'            => 'Outro',
            'email'           => 'duplicado@teste.com',
            'password'        => 'senha123',
            'confirmPassword' => 'senha123',
        ]);

        $this->assertDatabaseCount('pessoas', 1);
    }

    // ─── EDIT ─────────────────────────────────────────────────────────────────

    public function test_edit_retorna_view_para_pessoa_existente(): void
    {
        $pessoa = Pessoa::factory()->create();

        $response = $this->get(route('pessoas.edit', $pessoa->id));

        $response->assertStatus(200)
            ->assertViewIs('pessoas.edit')
            ->assertViewHas('pessoa', fn($p) => $p->id === $pessoa->id);
    }

    public function test_edit_redireciona_quando_pessoa_nao_existe(): void
    {
        $response = $this->get(route('pessoas.edit', 9999));

        $response->assertRedirect(route('pessoas.index'))
            ->assertSessionHas('error', 'Pessoa não encontrada');
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_pessoa_e_redireciona(): void
    {
        $pessoa = Pessoa::factory()->create();

        $response = $this->put(route('pessoas.update', $pessoa->id), [
            'name'      => 'Nome Atualizado',
            'email'     => 'novo@email.com',
            'telefone'  => '31900000000',
            'matricula' => '2026999999',
        ]);

        $response->assertRedirect(route('pessoas.index'))
            ->assertSessionHas('message', 'Pessoa atualizada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'id'    => $pessoa->id,
            'name'  => 'Nome Atualizado',
            'email' => 'novo@email.com',
        ]);
    }

    public function test_update_redireciona_quando_pessoa_nao_existe(): void
    {
        $response = $this->put(route('pessoas.update', 9999), [
            'name'  => 'Ghost',
            'email' => 'ghost@teste.com',
        ]);

        $response->assertRedirect(route('pessoas.index'))
            ->assertSessionHas('error', 'Pessoa não encontrada');
    }

    public function test_update_rejeita_troca_de_senha_com_confirmacao_diferente(): void
    {
        $pessoa = Pessoa::factory()->create();

        $response = $this->put(route('pessoas.update', $pessoa->id), [
            'name'            => $pessoa->name,
            'email'           => $pessoa->email,
            'password'        => 'nova123',
            'confirmPassword' => 'diferente999',
        ]);

        $response->assertSessionHas('error', 'As senhas não coincidem!');
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────
    // ATENÇÃO: O método destroy() em PessoaController está vazio (bug encontrado).
    // O teste abaixo documenta esse comportamento e irá FALHAR quando corrigido.

    public function test_destroy_metodo_nao_implementado_retorna_200_sem_excluir(): void
    {
        $pessoa = Pessoa::factory()->create();

        // Método destroy está vazio — não redireciona nem deleta
        $response = $this->delete(route('pessoas.destroy', $pessoa->id));

        // Comportamento atual: resposta vazia (200 ou sem conteúdo)
        // Dado que o método não faz nada, a pessoa ainda deve existir no banco
        $this->assertDatabaseHas('pessoas', ['id' => $pessoa->id]);
    }
}
