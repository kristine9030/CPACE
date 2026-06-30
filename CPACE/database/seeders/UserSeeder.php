<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Student accounts (role_id 2 = Role::STUDENT)
        User::updateOrCreate(['email' => 'test@cpace.com'], [
            'role_id'    => 2,
            'first_name' => 'Test',
            'last_name'  => 'User',
            'password'   => Hash::make('Password123'),
            'is_active'  => true,
        ]);

        User::updateOrCreate(['email' => 'john@cpace.com'], [
            'role_id'    => 2,
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'password'   => Hash::make('Password123'),
            'is_active'  => true,
        ]);

        // Faculty account (role_id 3 = Role::FACULTY)
        User::updateOrCreate(['email' => 'faculty@cpace.com'], [
            'role_id'    => 3,
            'first_name' => 'Faculty',
            'last_name'  => 'Admin',
            'password'   => Hash::make('Faculty123'),
            'is_active'  => true,
        ]);
    }
}
