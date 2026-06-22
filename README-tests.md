# Testes de Integração

## Cenários cobertos
- CRUD completo de usuários (criar, listar, atualizar, deletar).
- Validações de entrada (dados inválidos ou ausentes).
- Respostas HTTP adequadas (200, 201, 204, 404, 422).
- Regras de negócio: dados inválidos não são persistidos no banco.

## Problemas encontrados
- Nenhum até o momento. Caso novos endpoints sejam adicionados, será necessário expandir os testes.

## Execução
```bash
php artisan test
