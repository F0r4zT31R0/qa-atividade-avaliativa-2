<?php

namespace Tests\Feature;

use App\Models\Biblioteca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BibliotecasControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_view_with_filtered_bibliotecas(): void
    {
        $user = User::factory()->create();

        Biblioteca::create(['created_by' => $user->id, 'nome' => 'Biblioteca Central', 'endereco' => 'Rua A']);
        Biblioteca::create(['created_by' => $user->id, 'nome' => 'Biblioteca Zona Norte', 'endereco' => 'Rua B']);

        $response = $this->get(route('bibliotecas.index', ['nome' => 'Central']));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.index')
            ->assertViewHas('bibliotecas', function ($bibliotecas) {
                return $bibliotecas->count() === 1 && $bibliotecas->first()->nome === 'Biblioteca Central';
            });
    }

    public function test_create_returns_view_with_users(): void
    {
        User::factory()->create();

        $response = $this->get(route('bibliotecas.create'));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.new')
            ->assertViewHas('users');
    }

    public function test_store_creates_biblioteca_and_redirects(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('bibliotecas.store'), [
            'created_by' => $user->id,
            'nome' => 'Biblioteca Nova',
            'endereco' => 'Avenida Teste',
        ]);

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca criada com sucesso');

        $this->assertDatabaseHas('bibliotecas', [
            'nome' => 'Biblioteca Nova',
            'endereco' => 'Avenida Teste',
            'created_by' => $user->id,
        ]);
    }

    public function test_edit_existing_biblioteca_returns_new_view(): void
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create(['created_by' => $user->id, 'nome' => 'Biblioteca X', 'endereco' => 'Rua X']);

        $response = $this->get(route('bibliotecas.edit', ['id' => $biblioteca->id]));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.new')
            ->assertViewHas('biblioteca', function ($viewBiblioteca) use ($biblioteca) {
                return $viewBiblioteca->id === $biblioteca->id;
            });
    }

    public function test_edit_nonexistent_biblioteca_returns_error_view(): void
    {
        $response = $this->get(route('bibliotecas.edit', ['id' => 999]));

        $response->assertStatus(200)
            ->assertViewIs('bibliotecas.new')
            ->assertViewHas('error', 'Biblioteca não encontrada');
    }

    public function test_update_existing_biblioteca_updates_and_redirects(): void
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $biblioteca = Biblioteca::create(['created_by' => $user->id, 'nome' => 'Biblioteca Antiga', 'endereco' => 'Rua Antiga']);

        $response = $this->put(route('bibliotecas.update', ['id' => $biblioteca->id]), [
            'created_by' => $secondUser->id,
            'nome' => 'Biblioteca Atualizada',
            'endereco' => 'Rua Atualizada',
            'email' => 'contato@example.com',
        ]);

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca atualizada com sucesso');

        $this->assertDatabaseHas('bibliotecas', [
            'id' => $biblioteca->id,
            'nome' => 'Biblioteca Atualizada',
            'endereco' => 'Rua Atualizada',
            'email' => 'contato@example.com',
            'created_by' => 2,
        ]);
    }

    public function test_update_nonexistent_biblioteca_returns_404(): void
    {
        $response = $this->put(route('bibliotecas.update', ['id' => 999]), [
            'nome' => 'Não existe',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Biblioteca não encontrada']);
    }

    public function test_destroy_existing_biblioteca_deletes_and_redirects(): void
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create(['created_by' => $user->id, 'nome' => 'Biblioteca para excluir', 'endereco' => 'Rua Excluir']);

        $response = $this->delete(route('bibliotecas.destroy', ['id' => $biblioteca->id]));

        $response->assertRedirect(route('bibliotecas.index'))
            ->assertSessionHas('message', 'Biblioteca excluída com sucesso');

        $this->assertDatabaseMissing('bibliotecas', ['id' => $biblioteca->id]);
    }

    public function test_destroy_nonexistent_biblioteca_returns_404(): void
    {
        $response = $this->delete(route('bibliotecas.destroy', ['id' => 999]));

        $response->assertStatus(404)
            ->assertJson(['error' => 'Biblioteca não encontrada']);
    }
}
