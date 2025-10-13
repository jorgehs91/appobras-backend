<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        $roles = [
            'Admin Obra',
            'Engenheiro',
            'Financeiro',
            'Compras',
            'Prestador',
            'Leitor',
        ];

        $permissions = [
            // conjunto inicial reduzido por domínio
            'users.view', 'users.create', 'users.update', 'users.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'orders.view', 'orders.create', 'orders.update', 'orders.delete',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $roleInstances = [];
        foreach ($roles as $roleName) {
            $roleInstances[$roleName] = Role::findOrCreate($roleName, $guard);
        }

        // matriz reduzida de permissões por papel
        $matrix = [
            'Admin Obra' => $permissions,
            'Engenheiro' => [
                'projects.view', 'projects.create', 'projects.update',
                'orders.view', 'orders.create', 'orders.update',
            ],
            'Financeiro' => [
                'invoices.view', 'invoices.create', 'invoices.update',
                'projects.view',
            ],
            'Compras' => [
                'orders.view', 'orders.create', 'orders.update',
                'projects.view',
            ],
            'Prestador' => [
                'projects.view', 'orders.view',
            ],
            'Leitor' => [
                'projects.view', 'orders.view', 'invoices.view', 'users.view',
            ],
        ];

        foreach ($matrix as $roleName => $perms) {
            $roleInstances[$roleName]->syncPermissions($perms);
        }
    }
}


