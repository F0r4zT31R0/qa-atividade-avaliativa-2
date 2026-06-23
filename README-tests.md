
```markdown
# Testes de Integração — Atividade Avaliativa

##  Objetivo
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
Execute:
```bash
php artisan test
```
ou
```bash
vendor/bin/phpunit
```

---

##  Estrutura dos testes
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
````

#Erros comuns e soluções
- **`MissingAppKeyException`** → rodar `php artisan key:generate`.  
- **`View not found`** → criar a view correspondente em `resources/views/`.  
- **Banco não configurado** → ajustar `.env` com usuário e senha corretos.  
