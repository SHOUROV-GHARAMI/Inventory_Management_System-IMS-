<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $staffRole = Role::where('name', 'Staff')->first();

        // Create Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($superAdmin->roles->isEmpty()) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($admin->roles->isEmpty()) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($manager->roles->isEmpty()) {
            $manager->roles()->attach($managerRole->id);
        }

        // Create Staff User
        $staff = User::firstOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($staff->roles->isEmpty()) {
            $staff->roles()->attach($staffRole->id);
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Super Admin: superadmin@test.com / password');
        $this->command->info('Admin: admin@test.com / password');
        $this->command->info('Manager: manager@test.com / password');
        $this->command->info('Staff: staff@test.com / password');
    }
}
