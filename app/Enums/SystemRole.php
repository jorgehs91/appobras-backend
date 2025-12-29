<?php

namespace App\Enums;

/**
 * System roles - roles globais do sistema usando Spatie Permission.
 * 
 * Estas roles são definidas globalmente no sistema e definem permissões
 * de alto nível para os usuários em toda a aplicação.
 * 
 * Roles disponíveis:
 * - Admin Obra: Administrador com acesso completo ao sistema
 * - Engenheiro: Engenheiro com permissões técnicas
 * - Financeiro: Acesso a recursos financeiros e orçamentos
 * - Compras: Acesso a requisições e pedidos de compra
 * - Prestador: Prestador de serviços (acesso limitado)
 * - Leitor: Apenas visualização
 */
enum SystemRole: string
{
    case AdminObra = 'Admin Obra';
    case Engenheiro = 'Engenheiro';
    case Financeiro = 'Financeiro';
    case Compras = 'Compras';
    case Prestador = 'Prestador';
    case Leitor = 'Leitor';

    /**
     * Get all role values as array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a value is a valid system role.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Get roles that have budget/financial access.
     *
     * @return array<self>
     */
    public static function budgetAccessRoles(): array
    {
        return [self::Financeiro, self::AdminObra];
    }

    /**
     * Check if a role has budget/financial access.
     */
    public function hasBudgetAccess(): bool
    {
        return in_array($this, self::budgetAccessRoles(), true);
    }
}
