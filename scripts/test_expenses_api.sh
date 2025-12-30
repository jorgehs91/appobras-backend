#!/bin/bash

# Script de teste manual da API de Expenses
# Uso: ./scripts/test_expenses_api.sh

set -e  # Para em caso de erro

# Configurações
BASE_URL="${BASE_URL:-https://appobras.com.test}"
EMAIL="${EMAIL:jorgedshenrique@gmail.com}"
PASSWORD="${PASSWORD:Orzhov91!}"
COMPANY_ID="${COMPANY_ID:-1}"
PROJECT_ID="${PROJECT_ID:-1}"

# Cores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Teste Manual da API de Expenses ===${NC}\n"

# 1. Login
echo -e "${YELLOW}1. Fazendo login...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\",\"device_name\":\"test-script\"}")

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token // empty')

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo -e "${RED}❌ Erro no login. Verifique email e senha.${NC}"
  echo "Resposta: $LOGIN_RESPONSE"
  exit 1
fi

echo -e "${GREEN}✅ Login realizado com sucesso${NC}"
echo "Token: ${TOKEN:0:30}..."
echo ""

# 2. Listar expenses (pode estar vazio)
echo -e "${YELLOW}2. Listando expenses do projeto $PROJECT_ID...${NC}"
LIST_RESPONSE=$(curl -s -X GET "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID")

EXPENSE_COUNT=$(echo "$LIST_RESPONSE" | jq -r '.data | length')
echo -e "${GREEN}✅ Encontrados $EXPENSE_COUNT expense(s)${NC}"
echo ""

# 3. Criar expense draft (sem comprovante)
echo -e "${YELLOW}3. Criando expense draft...${NC}"
CREATE_DRAFT_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" \
  -H "Content-Type: application/json" \
  -d "{
    \"amount\": 1500.00,
    \"date\": \"$(date +%Y-%m-%d)\",
    \"description\": \"Expense criado via script de teste\",
    \"status\": \"draft\"
  }")

DRAFT_ID=$(echo "$CREATE_DRAFT_RESPONSE" | jq -r '.data.id // empty')

if [ -z "$DRAFT_ID" ] || [ "$DRAFT_ID" = "null" ]; then
  echo -e "${RED}❌ Erro ao criar expense draft${NC}"
  echo "Resposta: $CREATE_DRAFT_RESPONSE"
  exit 1
fi

echo -e "${GREEN}✅ Expense draft criado com ID: $DRAFT_ID${NC}"
echo ""

# 4. Visualizar expense criado
echo -e "${YELLOW}4. Visualizando expense $DRAFT_ID...${NC}"
VIEW_RESPONSE=$(curl -s -X GET "$BASE_URL/api/v1/expenses/$DRAFT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID")

EXPENSE_AMOUNT=$(echo "$VIEW_RESPONSE" | jq -r '.data.amount')
EXPENSE_STATUS=$(echo "$VIEW_RESPONSE" | jq -r '.data.status')
echo -e "${GREEN}✅ Expense encontrado:${NC}"
echo "  - Amount: $EXPENSE_AMOUNT"
echo "  - Status: $EXPENSE_STATUS"
echo ""

# 5. Atualizar expense
echo -e "${YELLOW}5. Atualizando expense $DRAFT_ID...${NC}"
UPDATE_RESPONSE=$(curl -s -X PUT "$BASE_URL/api/v1/expenses/$DRAFT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" \
  -H "Content-Type: application/json" \
  -d "{
    \"amount\": 2000.00,
    \"description\": \"Expense atualizado via script\"
  }")

UPDATED_AMOUNT=$(echo "$UPDATE_RESPONSE" | jq -r '.data.amount')
echo -e "${GREEN}✅ Expense atualizado. Novo amount: $UPDATED_AMOUNT${NC}"
echo ""

# 6. Testar filtros
echo -e "${YELLOW}6. Testando filtros (por status)...${NC}"
FILTER_RESPONSE=$(curl -s -X GET "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses?status=draft" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID")

FILTERED_COUNT=$(echo "$FILTER_RESPONSE" | jq -r '.data | length')
echo -e "${GREEN}✅ Encontrados $FILTERED_COUNT expense(s) com status draft${NC}"
echo ""

# 7. Tentar criar approved sem comprovante (deve falhar)
echo -e "${YELLOW}7. Tentando criar expense approved sem comprovante (deve falhar)...${NC}"
FAIL_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/api/v1/projects/$PROJECT_ID/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID" \
  -H "Content-Type: application/json" \
  -d "{
    \"amount\": 1500.00,
    \"date\": \"$(date +%Y-%m-%d)\",
    \"status\": \"approved\"
  }")

HTTP_CODE=$(echo "$FAIL_RESPONSE" | tail -n1)

if [ "$HTTP_CODE" = "422" ]; then
  echo -e "${GREEN}✅ Validação funcionando corretamente (422 retornado)${NC}"
else
  echo -e "${YELLOW}⚠️  Esperado 422, mas recebeu $HTTP_CODE${NC}"
fi
echo ""

# 8. Deletar expense criado
echo -e "${YELLOW}8. Deletando expense $DRAFT_ID...${NC}"
DELETE_RESPONSE=$(curl -s -w "%{http_code}" -X DELETE "$BASE_URL/api/v1/expenses/$DRAFT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-Company-Id: $COMPANY_ID")

DELETE_CODE=$(echo "$DELETE_RESPONSE" | tail -n1)

if [ "$DELETE_CODE" = "204" ]; then
  echo -e "${GREEN}✅ Expense deletado com sucesso${NC}"
else
  echo -e "${YELLOW}⚠️  Esperado 204, mas recebeu $DELETE_CODE${NC}"
fi
echo ""

echo -e "${BLUE}=== Teste concluído! ===${NC}"
echo ""
echo -e "${YELLOW}Notas:${NC}"
echo "- Para testar upload de arquivo, use curl com -F 'receipt=@arquivo.pdf'"
echo "- Verifique a documentação em docs/TESTE_MANUAL_EXPENSES_API.md"
echo ""

