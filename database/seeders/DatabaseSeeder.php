<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'username' => 'Test User',
            'full_name' => 'Test User',
            'current_task_status' => 'idle',
            'role' => 'member',
            'email' => 'test21@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
