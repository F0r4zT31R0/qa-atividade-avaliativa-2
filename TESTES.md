# Relatório de Testes de Integração — Atividade Avaliativa 2

## O que foi testado

### BibliotecasController
Arquivo: `tests/Feature/BibliotecasControllerTest.php` *(já fornecido no repositório base)*

- Listagem com filtro por nome
- Criação de biblioteca com dados válidos
- Edição de biblioteca existente e inexistente
- Atualização de biblioteca (existente e inexistente)
- Exclusão de biblioteca (existente e inexistente)

---

### UserController
Arquivo: `tests/Feature/UserControllerTest.php`

- Listagem de todos os usuários
- Exibição de usuário por ID (existente e inexistente)
- Formulário de criação
- Criação com dados válidos
- Bloqueio de e-mail duplicado (regra de unicidade do banco)
- Formulário de edição (existente e inexistente)
- Atualização de dados
- Exclusão (existente e inexistente)

---

### PessoaController
Arquivo: `tests/Feature/PessoaControllerTest.php`

- Listagem de pessoas
- Formulário de nova pessoa
- Criação com dados válidos
- Rejeição de senhas divergentes
- Bloqueio de e-mail duplicado
- Edição (existente e inexistente)
- Atualização de dados e validação de senhas
- Comportamento do método `destroy` (ver bugs abaixo)

---

### AutorController
Arquivo: `tests/Feature/AutorControllerTest.php`

- Listagem de autores
- Formulário de criação
- Criação válida (com e sem campos opcionais)
- Validação de `nome` obrigatório e comprimento máximo
- Validação de `data_nascimento` como data válida
- Edição (existente e inexistente)
- Atualização e validações
- 404 para autor inexistente na edição/atualização

---

### BibliotecaPessoaController
Arquivo: `tests/Feature/BibliotecaPessoaControllerTest.php`

- Formulário de associação exibe apenas pessoas não vinculadas
- Associação válida de pessoa à biblioteca
- Rejeição de `pessoa_id` ausente ou inválido
- Bloqueio de associação duplicada

---

### LivroController
Arquivo: `tests/Feature/LivroControllerTest.php`

- CRUD completo documentado como comportamento esperado
- **Atenção:** todos os testes deste controller falham pois o `LivroController` está vazio (ver bugs)

---

## Bugs / Problemas encontrados

| # | Controller | Problema | Impacto |
|---|-----------|---------|---------|
| 1 | `PessoaController` | Método `destroy()` está completamente vazio — não exclui nem redireciona | **Alto** — impossível excluir pessoas pelo sistema |
| 2 | `LivroController` | Controller totalmente vazio — nenhuma rota de livros funciona | **Alto** — CRUD de livros inoperante |
| 3 | `AutorController` | Model `Autor` tem campo `sobrenome` no `$fillable`, mas a migration não criou essa coluna | **Médio** — pode causar erro ao tentar salvar sobrenome |
| 4 | `AutorController` | Migration adiciona coluna `data_nascimento` (migration separada), mas a validação do controller aceita esse campo; o model não declara `data_nascimento` no `$fillable` explicitamente | **Baixo** — campo pode não ser persistido |
| 5 | `BibliotecasController` | Método `edit` retorna `view('bibliotecas.new')` em vez de `view('bibliotecas.edit')` | **Médio** — possível confusão de template |
| 6 | `UserController` | `store` não valida campos obrigatórios — nome, email e senha podem ser vazios | **Médio** — dados inválidos podem ser persistidos |

---

## Como executar os testes

```bash
# Configuração inicial
cp .env.example .env
composer install
php artisan key:generate

# Todos os testes
php artisan test

# Com cobertura de código
php artisan test --coverage

# Teste específico
php artisan test --filter AutorControllerTest
```

## CI/CD — GitHub Actions

O workflow em `.github/workflows/tests.yml` executa automaticamente a cada pull request para as branches `develop` e `master`.

Passos do pipeline:
1. Checkout do código
2. Setup PHP 8.2 com SQLite e Xdebug
3. Instalação das dependências via Composer
4. Geração da chave da aplicação
5. Execução das migrations em SQLite em memória
6. Execução dos testes com relatório de cobertura
