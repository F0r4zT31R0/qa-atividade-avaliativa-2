<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with a user created via terminal input.
     */
    public function run(): void
    {
        $name = $this->command->ask('Nome do usuário');
        $email = $this->command->ask('Email do usuário');
        $password = $this->command->secret('Senha do usuário');
        $role = $this->command->ask('Role do usuário', 'user');

        if (empty($name) || empty($email) || empty($password)) {
            $this->command->error('Nome, email e senha são obrigatórios.');
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command->error("Já existe um usuário com o email {$email}.");
            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->command->info("Usuário {$name} criado com sucesso.");
    }
}
