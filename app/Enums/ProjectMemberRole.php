<?php

namespace App\Enums;

/**
 * Project member roles - roles específicas por projeto.
 * 
 * Diferente das roles globais do Spatie Permission (Admin Obra, Engenheiro, etc.),
 * estas roles definem o papel do membro DENTRO de um projeto específico.
 * 
 * Exemplos de uso:
 * - Manager: Gerente do projeto, pode ter permissões especiais dentro do projeto
 * - Engenheiro: Engenheiro responsável pelo projeto
 * - Viewer: Apenas visualização, sem permissões de edição
 * - Fiscal: Fiscal de obra
 * - Coordenador: Coordenador de equipe no projeto
 */
enum ProjectMemberRole: string
{
    case Manager = 'Manager';
    case Engenheiro = 'Engenheiro';
    case Fiscal = 'Fiscal';
    case Coordenador = 'Coordenador';
    case Viewer = 'Viewer';

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
     * Check if a value is a valid role.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}

