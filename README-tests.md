# Testes de Integração — Atividade Avaliativa

## Objetivo

Este projeto implementa **testes de integração** para os principais controllers do sistema:

- UserController
- PessoaController
- LivroController
- AutorController
- BibliotecasController
- BibliotecaPessoaController

O objetivo é garantir que os fluxos de CRUD, validações e regras de negócio funcionem corretamente.

---

## Configuração inicial

1. Clone o repositório:
   ```bash
   git clone <url-do-repo>
   ```
2. Instale as dependências:
   ```bash
   composer install
   ```
3. Configure o `.env`:
   - Banco de dados
   - APP_KEY
   ```bash
   php artisan key:generate
   ```
4. Rode as migrations:
   ```bash
   php artisan migrate
   ```

---

## Como rodar os testes

```bash
php artisan test
```
ou
```bash
vendor/bin/phpunit
```

---

## Estrutura dos testes

- **UserControllerTest** → CRUD de usuários, validações de email duplicado.
- **PessoaControllerTest** → CRUD de pessoas, validações de senha.
- **LivroControllerTest** → CRUD de livros, validações de campos obrigatórios, views.
- **AutorControllerTest** → CRUD de autores, validações de nome e data.
- **BibliotecasControllerTest** → CRUD de bibliotecas, filtros.
- **BibliotecaPessoaControllerTest** → associação entre pessoas e bibliotecas.

---

## Integração contínua (CI)

O projeto está configurado com **GitHub Actions** para rodar os testes automaticamente em cada **Pull Request para a branch `develop`**.
Isso garante que nenhum código seja integrado sem passar pelos testes.

Exemplo de workflow (`.github/workflows/tests.yml`):

```yaml
name: Run Laravel Tests

on:
  pull_request:
    branches: [ "develop" ]

jobs:
  laravel-tests:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader

      - name: Generate app key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --force

      - name: Run tests
        run: php artisan test
```

---

## Relatório de Execução dos Testes

> Comando executado: `XDEBUG_MODE=coverage /usr/bin/php8.4 artisan test --coverage`
> Ambiente: Docker container `app_laravel` (PHP 8.4)
> **Resultado geral: 36 falhas / 30 aprovados — 108 assertivas — Duração: 6,14s**

---

### Resumo por suite

| Suite de Testes                    | Aprovados | Falhos | Total |
|------------------------------------|:---------:|:------:|:-----:|
| `Unit\ExampleTest`                 | 1         | 0      | 1     |
| `Feature\ExampleTest`              | 1         | 0      | 1     |
| `Feature\AutorControllerTest`      | 4         | 9      | 13    |
| `Feature\BibliotecaPessoaControllerTest` | 2   | 4      | 6     |
| `Feature\BibliotecasControllerTest`| 6         | 5      | 11    |
| `Feature\LivroControllerTest`      | 5         | 7      | 12    |
| `Feature\PessoaControllerTest`     | 5         | 4      | 9     |
| `Feature\UserControllerTest`       | 6         | 6      | 12    |
| **Total**                          | **30**    | **36** | **66**|

---

### Causa raiz principal: CSRF token ausente (HTTP 419)

A **grande maioria das falhas** (cerca de 30 dos 36 casos) ocorreu porque os testes de `POST`, `PUT` e `DELETE` não desabilitam a verificação CSRF do Laravel, resultando em resposta `419 Page Expired` ao invés do redirecionamento esperado.

**Solução:** Adicionar o trait `WithoutMiddleware` ou desabilitar especificamente o `VerifyCsrfToken` nas classes de teste, ou usar `$this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)` nos métodos relevantes.

```php
// Opção 1 — no topo da classe de teste
use Illuminate\Foundation\Testing\WithoutMiddleware;

// Opção 2 — método a método
$this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
```

---

### Detalhamento por controller

#### AutorControllerTest — 4 ✅ / 9 ❌

| Teste | Status | Causa |
|-------|--------|-------|
| index retorna view com autores | ✅ | — |
| create retorna view de novo autor | ✅ | — |
| edit retorna view correta para autor existente | ✅ | — |
| edit retorna 404 para autor inexistente | ✅ | — |
| store cria autor e redireciona | ❌ | 419 (CSRF) |
| store falha sem nome | ❌ | 419 → session sem `errors` |
| store falha com nome muito longo | ❌ | 419 → session sem `errors` |
| store aceita data nascimento valida | ❌ | 419 (CSRF) |
| store rejeita data nascimento invalida | ❌ | 419 → session sem `errors` |
| store aceita campos opcionais nulos | ❌ | 419 (CSRF) |
| update atualiza autor e redireciona | ❌ | 419 (CSRF) |
| update falha sem nome | ❌ | 419 → session sem `errors` |
| update retorna 404 para autor inexistente | ❌ | Recebeu 419, esperava 404 |

---

#### BibliotecaPessoaControllerTest — 2 ✅ / 4 ❌

| Teste | Status | Causa |
|-------|--------|-------|
| create retorna view com pessoas nao associadas | ✅ | — |
| create nao exibe pessoas ja associadas | ✅ | — |
| store associa pessoa a biblioteca | ❌ | 419 (CSRF) |
| store rejeita pessoa id invalido | ❌ | 419 → session sem `errors` |
| store rejeita pessoa id ausente | ❌ | 419 → session sem `errors` |
| store impede associacao duplicada | ❌ | 419 (CSRF) |

---

#### BibliotecasControllerTest — 6 ✅ / 5 ❌

| Teste | Status | Causa |
|-------|--------|-------|
| index retorna view com bibliotecas | ✅ | — |
| index filtra por nome | ✅ | — |
| create retorna view de nova biblioteca | ✅ | — |
| edit retorna view correta para biblioteca existente | ✅ | — |
| edit redireciona para index quando biblioteca nao existe | ✅ | — |
| store cria biblioteca e redireciona | ❌ | 419 (CSRF) |
| update atualiza biblioteca e redireciona | ❌ | 419 (CSRF) |
| update retorna 404 para biblioteca inexistente | ❌ | Recebeu 419, esperava 404 |
| destroy exclui biblioteca e redireciona | ❌ | 419 (CSRF) |
| destroy retorna 404 para biblioteca inexistente | ❌ | Recebeu 419, esperava 404 |

---

#### LivroControllerTest — 5 ✅ / 7 ❌

> **Nota:** Na primeira execução, o teste `index retorna view com livros` causou `Fatal error: Premature end of PHP process` por um erro de sintaxe em `LivroController.php` na linha 38. O problema foi corrigido antes da segunda execução.

| Teste | Status | Causa |
|-------|--------|-------|
| index retorna view com livros | ✅ | — |
| create retorna view com lista de autores | ✅ | — |
| show retorna view para livro existente | ✅ | — |
| show retorna 404 para livro inexistente | ✅ | — |
| edit retorna view para livro existente | ✅ | — |
| store cria livro e redireciona para index | ❌ | 419 (CSRF) |
| store nao valida campos obrigatorios | ❌ | Sem validação implementada: livro não é criado (count = 0, esperava 1) |
| edit retorna null para livro inexistente | ❌ | `Call to a member function all() on array` — controller não trata livro nulo |
| update atualiza livro e redireciona | ❌ | 419 (CSRF) |
| update retorna 404 para livro inexistente | ❌ | Recebeu 419, esperava 404 |
| destroy exclui livro e redireciona | ❌ | 419 (CSRF) |
| destroy retorna 404 para livro inexistente | ❌ | Recebeu 419, esperava 404 |

**Bugs identificados no `LivroController`:**
- **Linha 38:** erro de sintaxe (`T_VARIABLE`) que impedia análise estática.
- **Método `edit`:** usa `Livro::find()` sem verificar `null`, causando fatal error na view quando o livro não existe.
- **Método `store`:** ausência de validação dos campos obrigatórios.

---

#### PessoaControllerTest — 5 ✅ / 4 ❌

| Teste | Status | Causa |
|-------|--------|-------|
| index retorna view com pessoas | ✅ | — |
| create retorna view de nova pessoa | ✅ | — |
| store nao persiste pessoa com email duplicado | ✅ | — |
| edit retorna view para pessoa existente | ✅ | — |
| edit redireciona quando pessoa nao existe | ✅ | — |
| destroy metodo nao implementado retorna 200 sem excluir | ✅ | — |
| store cria pessoa e redireciona | ❌ | 419 (CSRF) |
| store rejeita senhas diferentes | ❌ | Session sem chave `error` — lógica de validação de senha não implementada ou retorno incorreto |
| update atualiza pessoa e redireciona | ❌ | 419 (CSRF) |
| update redireciona quando pessoa nao existe | ❌ | 419 (CSRF) |
| update rejeita troca de senha com confirmacao diferente | ❌ | Session sem chave `error` |

**Bugs identificados no `PessoaController`:**
- **Método `destroy`:** intencionalmente vazio (bug documentado nos testes).
- **Validação de senha:** a comparação entre `password` e `confirmPassword` não retorna a flash message de erro esperada (`As senhas não coincidem!`).

---

#### UserControllerTest — 6 ✅ / 6 ❌

| Teste | Status | Causa |
|-------|--------|-------|
| index retorna view com todos os usuarios | ✅ | — |
| show retorna view para usuario existente | ✅ | — |
| show redireciona quando usuario nao existe | ✅ | — |
| create retorna view de novo usuario | ✅ | — |
| edit retorna view para usuario existente | ✅ | — |
| edit redireciona quando usuario nao existe | ✅ | — |
| store cria usuario e redireciona | ❌ | 419 (CSRF) |
| store nao persiste usuario com email duplicado | ❌ | 419 (CSRF) — esperava redirecionamento com erro |
| update atualiza usuario e redireciona | ❌ | 419 (CSRF) |
| update retorna erro para usuario inexistente | ❌ | 419 (CSRF) |
| destroy exclui usuario e redireciona | ❌ | 419 (CSRF) |
| destroy retorna erro para usuario inexistente | ❌ | 419 (CSRF) |

---

### Bugs adicionais identificados

| Controller | Bug | Descrição |
|-----------|-----|-----------|
| `LivroController` | Sintaxe inválida | Erro `T_VARIABLE` na linha 38 impedia execução com coverage |
| `LivroController` | Método `edit` sem tratamento de null | Crash ao acessar livro inexistente |
| `LivroController` | Método `store` sem validação | Aceita campos obrigatórios vazios |
| `PessoaController` | Método `destroy` vazio | Não exclui registros (comportamento esperado pelo teste, mas classificado como bug) |
| `PessoaController` | Validação de senha fraca | Não retorna flash de erro ao comparar `password` ≠ `confirmPassword` |

---

## Erros comuns e soluções

| Erro | Solução |
|------|---------|
| `MissingAppKeyException` | Rodar `php artisan key:generate` |
| `View not found` | Criar a view em `resources/views/` |
| Banco não configurado | Ajustar `.env` com usuário e senha corretos |
| **HTTP 419 nos testes** | Desabilitar CSRF nos testes com `WithoutMiddleware` ou `withoutMiddleware(VerifyCsrfToken::class)` |
| `Call to a member function all() on array` | Verificar retorno nulo antes de chamar métodos (ex: `Livro::find()`) |
| `Session is missing expected key [errors]` | Implementar validação com `$request->validate([...])` no controller |
- **Banco não configurado** → ajustar `.env` com usuário e senha corretos.  
