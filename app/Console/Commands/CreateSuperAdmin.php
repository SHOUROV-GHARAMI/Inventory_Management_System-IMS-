<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin 
                            {--name= : The name of the super admin}
                            {--email= : The email of the super admin}
                            {--password= : The password of the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Super Admin user with complete system access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('');
        $this->info('===========================================');
        $this->info('   Create Super Admin User');
        $this->info('===========================================');
        $this->info('');

        // Check if Super Admin role exists
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if (!$superAdminRole) {
            $this->error('❌ Error: Super Admin role not found!');
            $this->error('Please run the seeder first: php artisan db:seed --class=RolePermissionSeeder');
            return 1;
        }

        // Get user input
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Validation errors:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }

        // Create Super Admin user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Assign Super Admin role
            $user->roles()->attach($superAdminRole->id);

            $this->info('');
            $this->info('✅ SUCCESS! Super Admin user created successfully!');
            $this->info('');
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Role', 'Super Admin'],
                    ['Permissions', $superAdminRole->permissions->count() . ' (ALL system permissions)'],
                    ['Created At', $user->created_at->format('Y-m-d H:i:s')],
                ]
            );
            $this->info('');
            $this->info('You can now login with these credentials.');
            $this->info('===========================================');
            $this->info('');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error creating user: ' . $e->getMessage());
            return 1;
        }
    }
}
