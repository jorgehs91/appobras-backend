# Guia de Teste Manual - API de Expenses

Este guia mostra como testar manualmente todos os endpoints de Expenses através da API.

## Pré-requisitos

1. **Usuário autenticado com role Financeiro ou Admin Obra**
2. **Company criada e associada ao usuário**
3. **Project criado na company**
4. **Token de autenticação (Bearer Token)**

---

## 1. Autenticação

### 1.1 Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "seu@email.com",
    "password": "sua_senha",
    "device_name": "postman"
  }'
```

**Resposta:**
```json
{
  "token": "1|abcdef123456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Seu Nome",
    "email": "seu@email.com"
  }
}
```

**Guarde o `token` para usar nos próximos requests.**

### 1.2 (Opcional) Registrar novo usuário

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuário Teste",
    "email": "teste@exemplo.com",
    "password": "senha123",
    "password_confirmation": "senha123",
    "device_name": "postman"
  }'
```

---

## 2. Configuração Inicial (se necessário)

### 2.1 Listar Companies

```bash
curl -X GET http://localhost:8000/api/v1/companies \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

### 2.2 Criar Company (se necessário)

```bash
curl -X POST http://localhost:8000/api/v1/companies \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Minha Empresa"
  }'
```

**Guarde o `id` da company retornado.**

### 2.3 Criar Project (se necessário)

```bash
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Projeto Teste",
    "description": "Descrição do projeto",
    "status": "planning"
  }'
```

**Guarde o `id` do project retornado.**

### 2.4 Atribuir role Financeiro ao usuário (via seeder ou banco)

O usuário precisa ter a role `Financeiro` ou `Admin Obra` para acessar expenses.

Você pode fazer isso via seeder ou diretamente no banco:

```sql
-- Obter o ID do usuário e company
-- Depois atribuir a role (o sistema usa Spatie Permission)
```

Ou criar via artisan tinker:
```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(1);
$company = \App\Models\Company::find(1);
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
$user->assignRole(\App\Enums\SystemRole::Financeiro->value);
```

---

## 3. Testando Expenses

### 3.1 Listar Expenses de um Project

```bash
curl -X GET "http://localhost:8000/api/v1/projects/1/expenses" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1"
```

**Filtros disponíveis:**
- `?status=draft` - Filtrar por status (draft ou approved)
- `?date_from=2025-12-01` - Despesas a partir desta data
- `?date_to=2025-12-31` - Despesas até esta data

**Exemplo com filtros:**
```bash
curl -X GET "http://localhost:8000/api/v1/projects/1/expenses?status=approved&date_from=2025-12-01&date_to=2025-12-31" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1"
```

---

### 3.2 Criar Expense (Draft - sem comprovante)

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1500.00,
    "date": "2025-12-29",
    "description": "Compra de materiais",
    "status": "draft"
  }'
```

**Nota:** O `project_id` vem da URL (`/projects/1/expenses`), não é necessário enviar no corpo da requisição.
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "cost_item_id": null,
    "project_id": 1,
    "amount": 1500.0,
    "date": "2025-12-29",
    "description": "Compra de materiais",
    "receipt_path": null,
    "status": "draft",
    "created_at": "2025-12-29T16:00:00.000000Z",
    "updated_at": "2025-12-29T16:00:00.000000Z"
  }
}
```

---

### 3.3 Criar Expense (Approved - com comprovante)

**Usando multipart/form-data para upload de arquivo:**

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -F "project_id=1" \
  -F "amount=2500.00" \
  -F "date=2025-12-29" \
  -F "description=Pagamento de fornecedor" \
  -F "status=approved" \
  -F "receipt=@/caminho/para/seu/comprovante.pdf"
```

**Nota:** O campo `receipt` deve ser um arquivo (PDF, JPG, JPEG ou PNG, máximo 10MB).

**Exemplo com arquivo local:**
```bash
# Criar um arquivo de teste primeiro
echo "Test receipt content" > /tmp/test_receipt.pdf

# Upload
curl -X POST http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -F "amount=2500.00" \
  -F "date=2025-12-29" \
  -F "description=Pagamento de fornecedor" \
  -F "status=approved" \
  -F "receipt=@/tmp/test_receipt.pdf"
```

---

### 3.4 Criar Expense com Cost Item associado

Primeiro, crie um Budget e Cost Item:

```bash
# Criar Budget
curl -X POST http://localhost:8000/api/v1/projects/1/budgets \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "total_planned": 100000.00
  }'

# Criar Cost Item (guarde o ID retornado)
curl -X POST http://localhost:8000/api/v1/projects/1/budgets/1/cost-items \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Materiais de Construção",
    "category": "Materiais",
    "planned_amount": 50000.00,
    "unit": "kg"
  }'

# Agora criar Expense associado ao Cost Item
curl -X POST http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "cost_item_id": 1,
    "amount": 1500.00,
    "date": "2025-12-29",
    "description": "Compra relacionada ao item de custo",
    "status": "draft"
  }'
```
```

---

### 3.5 Visualizar Expense específico

```bash
curl -X GET http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1"
```

---

### 3.6 Atualizar Expense

```bash
curl -X PUT http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 2000.00,
    "description": "Descrição atualizada",
    "date": "2025-12-30"
  }'
```

**Atualizar com novo comprovante:**
```bash
curl -X PUT http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -F "amount=2000.00" \
  -F "description=Descrição atualizada" \
  -F "status=approved" \
  -F "receipt=@/caminho/para/novo_comprovante.pdf"
```

---

### 3.7 Aprovar Expense (mudar de draft para approved)

**Importante:** Para mudar status para `approved`, é obrigatório ter um comprovante.

```bash
# Se já tem comprovante, apenas atualizar status
curl -X PUT http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "approved"
  }'
# Isso só funciona se o expense já tiver receipt_path

# Se não tem comprovante, precisa enviar junto
curl -X PUT http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -F "status=approved" \
  -F "receipt=@/caminho/para/comprovante.pdf"
```

---

### 3.8 Download do Comprovante

```bash
curl -X GET http://localhost:8000/api/v1/expenses/1/receipt \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  --output comprovante_baixado.pdf
```

---

### 3.9 Deletar Expense

```bash
curl -X DELETE http://localhost:8000/api/v1/expenses/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1"
```

**Resposta:** Status 204 (No Content)

---

## 4. Testando Validações

### 4.1 Tentar criar expense approved sem comprovante (deve falhar)

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "X-Company-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1500.00,
    "date": "2025-12-29",
    "status": "approved"
  }'
```
```

**Resposta esperada:** 422 (Validation Error)
```json
{
  "message": "O comprovante é obrigatório para despesas aprovadas.",
  "errors": {
    "receipt": ["O comprovante é obrigatório para despesas aprovadas."]
  }
}
```

### 4.2 Tentar criar expense sem permissão (deve retornar 403)

```bash
# Usando token de usuário sem role Financeiro/Admin Obra
curl -X GET http://localhost:8000/api/v1/projects/1/expenses \
  -H "Authorization: Bearer TOKEN_SEM_PERMISSAO" \
  -H "X-Company-Id: 1"
```

**Resposta esperada:** 403 (Forbidden)

---

## 5. Testando com Postman/Insomnia

### 5.1 Configuração

1. **Variáveis de ambiente:**
   - `base_url`: `http://localhost:8000`
   - `token`: (obtido no login)
   - `company_id`: `1`
   - `project_id`: `1`

2. **Headers padrão:**
   - `Authorization`: `Bearer {{token}}`
   - `X-Company-Id`: `{{company_id}}`

### 5.2 Collection exemplo (Postman)

Importe esta collection JSON no Postman:

```json
{
  "info": {
    "name": "Expenses API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"teste@exemplo.com\",\n  \"password\": \"senha123\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/v1/auth/login",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "auth", "login"]
        }
      }
    },
    {
      "name": "List Expenses",
      "request": {
        "method": "GET",
        "header": [
          {"key": "Authorization", "value": "Bearer {{token}}"},
          {"key": "X-Company-Id", "value": "{{company_id}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/projects/{{project_id}}/expenses",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "projects", "{{project_id}}", "expenses"]
        }
      }
    },
    {
      "name": "Create Expense",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Authorization", "value": "Bearer {{token}}"},
          {"key": "X-Company-Id", "value": "{{company_id}}"}
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {"key": "project_id", "value": "{{project_id}}", "type": "text"},
            {"key": "amount", "value": "1500.00", "type": "text"},
            {"key": "date", "value": "2025-12-29", "type": "text"},
            {"key": "description", "value": "Compra de materiais", "type": "text"},
            {"key": "status", "value": "approved", "type": "text"},
            {"key": "receipt", "type": "file", "src": "/caminho/para/arquivo.pdf"}
          ]
        },
        "url": {
          "raw": "{{base_url}}/api/v1/projects/{{project_id}}/expenses",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "projects", "{{project_id}}", "expenses"]
        }
      }
    }
  ]
}
```

---

## 6. Swagger/OpenAPI Documentation

Se o projeto estiver configurado com Swagger/Scribe, você pode acessar a documentação interativa em:

```
http://localhost:8000/api/documentation
```

Lá você encontrará todos os endpoints documentados e poderá testá-los diretamente pela interface.

---

## 7. Verificando arquivos no Storage

### 7.1 Local (padrão)

Os arquivos são salvos em:
```
storage/app/private/expenses/project-{id}/nome_do_arquivo.pdf
```

Para verificar:
```bash
ls -la storage/app/private/expenses/
```

### 7.2 S3 (se configurado)

Se configurado `EXPENSE_RECEIPTS_DISK=s3` no `.env`, os arquivos serão salvos no bucket S3 configurado.

---

## 8. Troubleshooting

### Erro 401 (Unauthorized)
- Verifique se o token está correto e não expirou
- Faça login novamente para obter um novo token

### Erro 403 (Forbidden)
- Verifique se o usuário tem role `Financeiro` ou `Admin Obra`
- Verifique se o `X-Company-Id` está correto
- Verifique se o usuário pertence à company informada

### Erro 422 (Validation Error)
- Verifique se todos os campos obrigatórios foram enviados
- Para status `approved`, é obrigatório ter comprovante
- Verifique os tipos de arquivo permitidos (PDF, JPG, JPEG, PNG)
- Verifique o tamanho máximo do arquivo (10MB)

### Erro 404 (Not Found)
- Verifique se o ID do expense/project existe
- Verifique se o expense pertence à company/project informados

---

## 9. Script de Teste Completo

Crie um script bash `test_expenses.sh`:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000"
EMAIL="teste@exemplo.com"
PASSWORD="senha123"
COMPANY_ID=1
PROJECT_ID=1

echo "1. Login..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')
echo "Token obtido: ${TOKEN:0:20}..."

echo -e "\n2. Listar expenses..."
curl -s -X GET "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" | jq .

echo -e "\n3. Criar expense draft..."
EXPENSE_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": '$PROJECT_ID',
            "amount": 1500.00,
            "date": "2025-12-29",
            "description": "Teste de despesa",
            // project_id vem da URL (/projects/1/expenses), não precisa enviar
    "status": "draft"
  }')

EXPENSE_ID=$(echo $EXPENSE_RESPONSE | jq -r '.data.id')
echo "Expense criado com ID: $EXPENSE_ID"

echo -e "\n4. Visualizar expense..."
curl -s -X GET "$BASE_URL/api/v1/expenses/$EXPENSE_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" | jq .

echo -e "\n5. Deletar expense..."
curl -s -X DELETE "$BASE_URL/api/v1/expenses/$EXPENSE_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID"

echo -e "\nTeste concluído!"
```

**Tornar executável:**
```bash
chmod +x test_expenses.sh
./test_expenses.sh
```

---

## 10. Dicas Finais

1. **Use variáveis de ambiente** para facilitar os testes
2. **Salve o token** em uma variável para reutilizar
3. **Use `jq`** para formatar respostas JSON (instale com `brew install jq` no Mac)
4. **Teste os casos de erro** para garantir que as validações estão funcionando
5. **Verifique os logs** em `storage/logs/laravel.log` se algo não funcionar

