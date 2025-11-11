<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->count(5)->create();
        
        // Project::create([
        //     'project_name' => 'Project 1',
        //     'description' => 'lorem ipsum dolor sit amet',
        //     'created_by' => '1',
        //     'deadline' => '2028-01-01 10:00:00',
        //     'created_at' => '2024-01-01 10:00:00',
        // ]);
    }
}
