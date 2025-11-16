<?php

namespace Database\Seeders;

use App\Models\User;
use App\View\Components\ui\board\BoardCard;
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
        $this->call(TestDataSeeder::class);
        // $this->call(ProjectSeeder::class); // Disabled temporarily - needs Faker
        // $this->call(BoardCardSeeder::class); // Disabled temporarily - needs Faker

        User::create([
            'username' => 'Test User',
            'full_name' => 'Test User',
            'current_task_status' => 'idle',
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'phone' => null, // Optional
            'profile_picture' => null, // Optional
            'bio' => 'Administrator of the system', // Optional
        ]);
    }
}
