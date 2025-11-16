<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $users = [
            [
                'full_name' => 'John Doe',
                'email' => 'john@example.com', 
                'username' => 'johndoe',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'member',
                'current_task_status' => 'idle',
                'phone' => '+62812345678',
                'profile_picture' => null,
                'bio' => 'Experienced project manager and team leader',
            ],
            [
                'full_name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'username' => 'janesmith', 
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'member',
                'current_task_status' => 'idle',
                'phone' => '+62812345679',
                'profile_picture' => null,
                'bio' => 'Full-stack developer specializing in Laravel and Vue.js',
            ],
            [
                'full_name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'username' => 'bobwilson',
                'password' => Hash::make('password'), 
                'email_verified_at' => now(),
                'role' => 'member',
                'current_task_status' => 'idle',
                'phone' => '+62812345680',
                'profile_picture' => null,
                'bio' => 'Backend developer with expertise in API development',
            ],
            [
                'full_name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'username' => 'alicebrown',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'member',
                'current_task_status' => 'idle',
                'phone' => '+62812345681',
                'profile_picture' => null,
                'bio' => 'UI/UX designer passionate about creating beautiful interfaces',
            ],
            [
                'full_name' => 'Charlie Davis',
                'email' => 'charlie@example.com',
                'username' => 'charliedavis',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'member',
                'current_task_status' => 'idle',
                'phone' => '+62812345682',
                'profile_picture' => null,
                'bio' => 'Frontend developer specializing in modern JavaScript frameworks',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
        }

        // Create test project menggunakan Eloquent Model (bukan raw SQL)
        // Ini akan trigger Observer untuk auto-generate slug
        $project = Project::firstOrCreate(
            ['project_name' => 'Test Project Management App'],
            [
                'project_name' => 'Test Project Management App',
                'description' => 'A sample project for testing the project management application',
                'created_by' => 1, // John Doe
                'deadline' => now()->addDays(30),
                'created_at' => now(),
            ]
        );
        
        $projectId = $project->id;

        // Create project members
        $members = [
            ['user_id' => 1, 'role' => 'team lead'],
            ['user_id' => 2, 'role' => 'developer'],
            ['user_id' => 3, 'role' => 'developer'],
            ['user_id' => 4, 'role' => 'designer'],
        ];

        foreach ($members as $memberData) {
            ProjectMember::firstOrCreate(
                [
                    'project_id' => $projectId,
                    'user_id' => $memberData['user_id']
                ],
                [
                    'project_id' => $projectId,
                    'user_id' => $memberData['user_id'],
                    'role' => $memberData['role'],
                    'joined_at' => now(),
                ]
            );
        }
    }
}