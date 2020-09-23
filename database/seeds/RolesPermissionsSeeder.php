<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    private function createPermissions()
    {
        $permissionsContext = ['role', 'permission', 'user', 'food', 'order', 'transaction', 'table', 'category'];
        $permissionsAbilities = ['index', 'show', 'create', 'update', 'delete', 'import', 'export'];

        foreach ($permissionsContext as $permissionName) {
            foreach ($permissionsAbilities as $permissionAbility) {
                Permission::create([
                    'name' => $permissionName . '.' . $permissionAbility,
                ]);
            }
        }
    }

    private function createSpecialPermissions()
    {
        //
    }

    private function createRoles()
    {
        $roles = ['admin', 'waiter', 'cashier', 'customer', 'owner'];
        $permissions = [
            'waiter' => [
                'food.index', 'food.show', 'food.import', 'food.excel',
                'order.index', 'order.show', 'order.update', 'order.delete',
                'table.index', 'table.show', 'table.update', 'table.delete',
            ],
            'cashier' => [
                'transaction.index', 'transaction.create', 'transaction.show', 'transaction.import', 'transaction.export',
            ],
            'customer' => [
                'food.index', 'food.show',
                'category.index',
                'order.create',
            ],
            'owner' => [
                'food.index', 'food.show', 'food.import', 'food.export',
                'order.index', 'order.show', 'order.import', 'order.export',
                'user.index', 'user.show', 'user.import', 'user.export',
                'transaction.index', 'transaction.import', 'transaction.export',
                'role.index', 'role.show', 'role.import', 'role.export',
                'permissions.index', 'permissions.show', 'permissions.import', 'permissions.export',
                'category.index', 'category.import', 'category.export'
            ]
        ];

        foreach ($roles as $roleName) {
            $role = Role::create([
                'name' => $roleName
            ]);

            switch ($roleName) {
                case 'admin':
                    $role->syncPermissions(
                        Permission::select('id')->get()->pluck('id')
                    );
                    break;
                case 'waiter':
                    $permissionsIds = Permission::select('id')->whereIn('name', $permissions['waiter'])->get()->pluck('id');
                    $role->syncPermissions($permissionsIds);
                    break;
                case 'cashier':
                    $permissionsIds = Permission::select('id')->whereIn('name', $permissions['cashier'])->get()->pluck('id');
                    $role->syncPermissions($permissionsIds);
                    break;
                case 'customer':
                    $permissionsIds = Permission::select('id')->whereIn('name', $permissions['customer'])->get()->pluck('id');
                    $role->syncPermissions($permissionsIds);
                    break;
                case 'owner':
                    $permissionsIds = Permission::select('id')->whereIn('name', $permissions['owner'])->get()->pluck('id');
                    $role->syncPermissions($permissionsIds);
                    break;
            }
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createPermissions();
        $this->createSpecialPermissions();
        $this->createRoles();
    }
}
