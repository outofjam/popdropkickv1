<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'testuser@mac.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'), // change if you like
            ]
        );
    }
}
