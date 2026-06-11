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
        // Student accounts
        User::updateOrCreate(['email' => 'test@cpace.com'], [
            'name' => 'Test User',
            'role' => 'student',
            'password' => Hash::make('Password123'),
        ]);

        User::updateOrCreate(['email' => 'john@cpace.com'], [
            'name' => 'John Doe',
            'role' => 'student',
            'password' => Hash::make('Password123'),
        ]);

        // Faculty account
        User::updateOrCreate(['email' => 'faculty@cpace.com'], [
            'name' => 'Faculty Admin',
            'role' => 'faculty',
            'password' => Hash::make('Faculty123'),
        ]);
    }
}
