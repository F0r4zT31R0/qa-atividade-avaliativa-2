<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_index_retorna_view_com_todos_os_usuarios(): void
    {
        User::factory()->count(3)->create();

        $response = $this->get(route('users.index'));

        $response->assertStatus(200)
            ->assertViewIs('users.index')
            ->assertViewHas('users', function ($users) {
                return $users->count() === 3;
            });
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_retorna_view_para_usuario_existente(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.show', $user->id));

        $response->assertStatus(200)
            ->assertViewIs('users.show')
            ->assertViewHas('user', fn($u) => $u->id === $user->id);
    }

    public function test_show_redireciona_quando_usuario_nao_existe(): void
    {
        $response = $this->get(route('users.show', 9999));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Usuário não encontrado');
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function test_create_retorna_view_de_novo_usuario(): void
    {
        $response = $this->get(route('users.create'));

        $response->assertStatus(200)
            ->assertViewIs('users.new');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_cria_usuario_e_redireciona(): void
    {
        $response = $this->post(route('users.store'), [
            'name'     => 'João Silva',
            'email'    => 'joao@teste.com',
            'password' => 'senha123',
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('message', 'Usuário criado com sucesso');

        $this->assertDatabaseHas('users', [
            'name'  => 'João Silva',
            'email' => 'joao@teste.com',
        ]);
    }

    public function test_store_nao_persiste_usuario_com_email_duplicado(): void
    {
        User::factory()->create(['email' => 'duplicado@teste.com']);

        $response = $this->post(route('users.store'), [
            'name'     => 'Outro Nome',
            'email'    => 'duplicado@teste.com',
            'password' => 'senha123',
        ]);

        // Espera redirecionamento de erro (email unique no banco)
        $response->assertRedirect();
        $this->assertDatabaseCount('users', 1);
    }

    // ─── EDIT ─────────────────────────────────────────────────────────────────

    public function test_edit_retorna_view_para_usuario_existente(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.edit', $user->id));

        $response->assertStatus(200)
            ->assertViewIs('users.edit')
            ->assertViewHas('user', fn($u) => $u->id === $user->id);
    }

    public function test_edit_redireciona_quando_usuario_nao_existe(): void
    {
        $response = $this->get(route('users.edit', 9999));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Usuário não encontrado');
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_atualiza_usuario_e_redireciona(): void
    {
        $user = User::factory()->create();

        $response = $this->put(route('users.update', $user->id), [
            'name'  => 'Nome Atualizado',
            'email' => 'atualizado@teste.com',
            'role'  => 'admin',
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('message', 'Usuário atualizado com sucesso');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nome Atualizado',
            'email' => 'atualizado@teste.com',
        ]);
    }

    public function test_update_retorna_erro_para_usuario_inexistente(): void
    {
        $response = $this->put(route('users.update', 9999), [
            'name'  => 'Ninguém',
            'email' => 'nao@existe.com',
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Usuário não encontrado');
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_exclui_usuario_e_redireciona(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('users.destroy', $user->id));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('message', 'Usuário excluído com sucesso');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_destroy_retorna_erro_para_usuario_inexistente(): void
    {
        $response = $this->delete(route('users.destroy', 9999));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Usuário não encontrado');
    }
}
