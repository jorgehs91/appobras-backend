#!/bin/bash

# Script para verificar se os testes estão configurados corretamente
# para não usar o banco de dados de desenvolvimento

echo "🔍 Verificando configuração de testes..."
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# 1. Verificar phpunit.xml
echo "1️⃣  Verificando phpunit.xml..."
if grep -q 'DB_CONNECTION.*sqlite' phpunit.xml && grep -q 'DB_DATABASE.*:memory:' phpunit.xml; then
    echo -e "${GREEN}✅ phpunit.xml está configurado corretamente (SQLite em memória)${NC}"
else
    echo -e "${RED}❌ phpunit.xml não está configurado para SQLite em memória${NC}"
fi
echo ""

# 2. Verificar se há .env.testing
echo "2️⃣  Verificando .env.testing..."
if [ -f ".env.testing" ]; then
    echo -e "${YELLOW}⚠️  .env.testing encontrado${NC}"
    echo "Conteúdo da configuração de banco:"
    grep -E "DB_" .env.testing | grep -v "PASSWORD"
else
    echo -e "${GREEN}✅ .env.testing não existe (usando phpunit.xml)${NC}"
fi
echo ""

# 3. Verificar .env principal (apenas para referência)
echo "3️⃣  Verificando .env principal (referência)..."
if [ -f ".env" ]; then
    DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
    echo "Banco de desenvolvimento configurado: $DB_NAME"
    echo -e "${YELLOW}⚠️  Este banco NÃO deve ser usado nos testes${NC}"
else
    echo -e "${YELLOW}⚠️  .env não encontrado${NC}"
fi
echo ""

# 4. Verificar se há variáveis de ambiente setadas
echo "4️⃣  Verificando variáveis de ambiente ativas..."
if [ -n "$DB_CONNECTION" ]; then
    echo -e "${YELLOW}⚠️  DB_CONNECTION está setado: $DB_CONNECTION${NC}"
    echo "   Isso pode sobrescrever phpunit.xml!"
else
    echo -e "${GREEN}✅ DB_CONNECTION não está setado (phpunit.xml será usado)${NC}"
fi
echo ""

# 5. Verificar comando recomendado
echo "5️⃣  Comandos para rodar testes:"
echo -e "${GREEN}✅ Correto:${NC} php artisan test"
echo -e "${GREEN}✅ Correto:${NC} php vendor/bin/phpunit"
echo -e "${RED}❌ ERRADO:${NC} php artisan test --env=local"
echo -e "${RED}❌ ERRADO:${NC} php artisan test --env=development"
echo ""

# 6. Sugestão de teste rápido
echo "6️⃣  Teste rápido recomendado:"
echo "   php artisan config:clear"
echo "   php artisan cache:clear"
echo "   php artisan test --filter ExpenseControllerTest::test_listar_expenses_requer_permissao"
echo ""

echo "📝 Documentação completa: docs/TESTES_BANCO_DADOS.md"
echo ""

