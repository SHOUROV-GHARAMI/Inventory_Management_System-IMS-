<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Product permissions
            ['name' => 'products.view', 'description' => 'View products'],
            ['name' => 'products.create', 'description' => 'Create products'],
            ['name' => 'products.update', 'description' => 'Update products'],
            ['name' => 'products.delete', 'description' => 'Delete products'],
            
            // Category permissions
            ['name' => 'categories.view', 'description' => 'View categories'],
            ['name' => 'categories.create', 'description' => 'Create categories'],
            ['name' => 'categories.update', 'description' => 'Update categories'],
            ['name' => 'categories.delete', 'description' => 'Delete categories'],
            
            // Supplier permissions
            ['name' => 'suppliers.view', 'description' => 'View suppliers'],
            ['name' => 'suppliers.create', 'description' => 'Create suppliers'],
            ['name' => 'suppliers.update', 'description' => 'Update suppliers'],
            ['name' => 'suppliers.delete', 'description' => 'Delete suppliers'],
            
            // Purchase order permissions
            ['name' => 'purchase-orders.view', 'description' => 'View purchase orders'],
            ['name' => 'purchase-orders.create', 'description' => 'Create purchase orders'],
            ['name' => 'purchase-orders.update', 'description' => 'Update purchase orders'],
            ['name' => 'purchase-orders.delete', 'description' => 'Delete purchase orders'],
            ['name' => 'purchase-orders.approve', 'description' => 'Approve purchase orders'],
            
            // Sales permissions
            ['name' => 'sales.view', 'description' => 'View sales'],
            ['name' => 'sales.create', 'description' => 'Create sales'],
            ['name' => 'sales.update', 'description' => 'Update sales'],
            ['name' => 'sales.delete', 'description' => 'Delete sales'],
            
            // Inventory permissions
            ['name' => 'inventory.view', 'description' => 'View inventory'],
            ['name' => 'inventory.adjust', 'description' => 'Adjust inventory'],
            
            // Report permissions
            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'reports.export', 'description' => 'Export reports'],
            
            // User management permissions
            ['name' => 'users.view', 'description' => 'View users'],
            ['name' => 'users.create', 'description' => 'Create users'],
            ['name' => 'users.update', 'description' => 'Update users'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            
            // Role management permissions
            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.manage', 'description' => 'Manage roles and permissions'],
            
            // System management permissions (Super Admin only)
            ['name' => 'system.settings', 'description' => 'Manage system settings'],
            ['name' => 'system.backup', 'description' => 'Create and restore backups'],
            ['name' => 'system.audit', 'description' => 'View and manage audit logs'],
            ['name' => 'system.health', 'description' => 'View system health'],
            ['name' => 'system.users.impersonate', 'description' => 'Impersonate other users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Create Roles
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Super Administrator with complete system access including settings, backups, and audit logs']
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator with full access to business operations']
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager'],
            ['description' => 'Manager with limited administrative access']
        );

        $staffRole = Role::firstOrCreate(
            ['name' => 'Staff'],
            ['description' => 'Staff member with basic access']
        );

        // Assign ALL permissions to Super Admin (including system-level)
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Assign business permissions to Admin (excluding system-level)
        $adminPermissions = Permission::whereNotIn('name', [
            'system.settings',
            'system.backup',
            'system.audit',
            'system.users.impersonate',
        ])->get();
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // Assign specific permissions to Manager
        $managerPermissions = Permission::whereIn('name', [
            'products.view', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'suppliers.view', 'suppliers.create', 'suppliers.update',
            'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.update', 'purchase-orders.approve',
            'sales.view', 'sales.create', 'sales.update',
            'inventory.view', 'inventory.adjust',
            'reports.view', 'reports.export',
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        // Assign specific permissions to Staff
        $staffPermissions = Permission::whereIn('name', [
            'products.view',
            'categories.view',
            'suppliers.view',
            'purchase-orders.view',
            'sales.view', 'sales.create',
            'inventory.view',
        ])->get();
        $staffRole->permissions()->sync($staffPermissions->pluck('id'));
    }
}
