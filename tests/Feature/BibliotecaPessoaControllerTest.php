<?php

namespace Tests\Feature;

use App\Models\Biblioteca;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BibliotecaPessoaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function criarBiblioteca(): Biblioteca
    {
        $user = User::factory()->create();
        return Biblioteca::create([
            'created_by' => $user->id,
            'nome'       => 'Biblioteca Teste',
            'endereco'   => 'Rua Teste',
        ]);
    }

    // ─── CREATE (formulário) ──────────────────────────────────────────────────

    public function test_create_retorna_view_com_pessoas_nao_associadas(): void
    {
        $biblioteca = $this->criarBiblioteca();
        Pessoa::factory()->count(3)->create();

        $response = $this->get(route('bibliotecas.pessoas.create', $biblioteca->id));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.add_pessoa')
            ->assertViewHas('biblioteca')
            ->assertViewHas('pessoas', fn($p) => $p->count() === 3);
    }

    public function test_create_nao_exibe_pessoas_ja_associadas(): void
    {
        $biblioteca = $this->criarBiblioteca();
        $associada  = Pessoa::factory()->create();
        $livre      = Pessoa::factory()->create();

        $biblioteca->pessoas()->attach($associada->id);

        $response = $this->get(route('bibliotecas.pessoas.create', $biblioteca->id));

        $response->assertViewHas('pessoas', function ($pessoas) use ($associada) {
            return $pessoas->doesntContain('id', $associada->id);
        });
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_associa_pessoa_a_biblioteca(): void
    {
        $biblioteca = $this->criarBiblioteca();
        $pessoa     = Pessoa::factory()->create();

        $response = $this->post(
            route('bibliotecas.pessoas.store', $biblioteca->id),
            ['pessoa_id' => $pessoa->id]
        );

        $response->assertRedirect(route('bibliotecas.edit', ['id' => $biblioteca->id]))
            ->assertSessionHas('message', 'Pessoa adicionada à biblioteca com sucesso.');

        $this->assertDatabaseHas('biblioteca_pessoa', [
            'biblioteca_id' => $biblioteca->id,
            'pessoa_id'     => $pessoa->id,
        ]);
    }

    public function test_store_rejeita_pessoa_id_invalido(): void
    {
        $biblioteca = $this->criarBiblioteca();

        $response = $this->post(
            route('bibliotecas.pessoas.store', $biblioteca->id),
            ['pessoa_id' => 9999]
        );

        $response->assertSessionHasErrors('pessoa_id');
    }

    public function test_store_rejeita_pessoa_id_ausente(): void
    {
        $biblioteca = $this->criarBiblioteca();

        $response = $this->post(
            route('bibliotecas.pessoas.store', $biblioteca->id),
            []
        );

        $response->assertSessionHasErrors('pessoa_id');
    }

    public function test_store_impede_associacao_duplicada(): void
    {
        $biblioteca = $this->criarBiblioteca();
        $pessoa     = Pessoa::factory()->create();
        $biblioteca->pessoas()->attach($pessoa->id);

        $response = $this->post(
            route('bibliotecas.pessoas.store', $biblioteca->id),
            ['pessoa_id' => $pessoa->id]
        );

        $response->assertRedirect()
            ->assertSessionHas('error', 'Pessoa já está associada a esta biblioteca.');

        // Deve existir apenas 1 registro no pivot
        $this->assertDatabaseCount('biblioteca_pessoa', 1);
    }
}
