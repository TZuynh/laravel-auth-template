<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function permissionMatrix(): array
    {
        $roles = [
            ['key' => 'admin', 'label' => __('messages.roles.admin'), 'count' => $this->countUsers(['administrator', 'admin']), 'tone' => 'purple'],
            ['key' => 'manager', 'label' => __('messages.roles.manager'), 'count' => $this->countUsers(['manager']), 'tone' => 'indigo'],
            ['key' => 'staff', 'label' => __('messages.roles.staff'), 'count' => $this->countUsers(['staff']), 'tone' => 'blue'],
            ['key' => 'customer', 'label' => __('messages.roles.customer'), 'count' => $this->countUsers(['customer']), 'tone' => 'slate'],
        ];

        $modules = [
            ['key' => 'dashboard', 'label' => __('messages.roles.module_dashboard')],
            ['key' => 'orders', 'label' => __('messages.roles.module_orders')],
            ['key' => 'quotations', 'label' => __('messages.roles.module_quotations')],
            ['key' => 'production', 'label' => __('messages.roles.module_production')],
            ['key' => 'inventory', 'label' => __('messages.roles.module_inventory')],
            ['key' => 'crm', 'label' => __('messages.roles.module_crm')],
            ['key' => 'hrm', 'label' => __('messages.roles.module_hrm')],
            ['key' => 'payroll', 'label' => __('messages.roles.module_payroll')],
            ['key' => 'reports', 'label' => __('messages.roles.module_reports')],
            ['key' => 'system', 'label' => __('messages.roles.module_system')],
        ];

        return [
            'roles' => $roles,
            'modules' => collect($modules)->map(function (array $module): array {
                return [
                    ...$module,
                    'permissions' => [
                        'admin' => ['view' => true, 'edit' => true, 'delete' => true],
                        'manager' => ['view' => true, 'edit' => in_array($module['key'], ['dashboard', 'orders', 'production', 'inventory', 'crm', 'hrm', 'reports'], true), 'delete' => false],
                        'staff' => ['view' => in_array($module['key'], ['dashboard', 'orders', 'inventory', 'crm', 'hrm'], true), 'edit' => in_array($module['key'], ['orders', 'inventory', 'crm'], true), 'delete' => false],
                        'customer' => ['view' => in_array($module['key'], ['dashboard', 'orders'], true), 'edit' => false, 'delete' => false],
                    ],
                ];
            })->all(),
            'actions' => [
                'view' => __('messages.roles.permission_view'),
                'edit' => __('messages.roles.permission_edit'),
                'delete' => __('messages.roles.permission_delete'),
            ],
        ];
    }

    private function countUsers(array $roles): int
    {
        return User::query()
            ->whereIn('role', $roles)
            ->count();
    }
}
