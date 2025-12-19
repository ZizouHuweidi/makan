<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage_listings',
            'approve_bookings',
            'moderate_reviews',
            'manage_users',
            'manage_amenities',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $hostRole = Role::firstOrCreate(['name' => 'host']);
        $hostRole->givePermissionTo(['manage_listings']);

        $guestRole = Role::firstOrCreate(['name' => 'guest']);

        $supportRole = Role::firstOrCreate(['name' => 'support']);
        $supportRole->givePermissionTo(['approve_bookings', 'moderate_reviews']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@makan.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create sample host
        $host = User::firstOrCreate(
            ['email' => 'host@makan.com'],
            [
                'name' => 'Host User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $host->assignRole('host');

        // Create sample guest
        $guest = User::firstOrCreate(
            ['email' => 'guest@makan.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $guest->assignRole('guest');
    }
}
