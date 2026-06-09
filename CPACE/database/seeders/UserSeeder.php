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
        // Create test account
        User::create([
            'name' => 'Test User',
            'email' => 'test@cpace.com',
            'password' => Hash::make('Password123'),
        ]);

        // Create additional test account
        User::create([
            'name' => 'John Doe',
            'email' => 'john@cpace.com',
            'password' => Hash::make('Password123'),
        ]);
    }
}
