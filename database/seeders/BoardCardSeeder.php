<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class BoardCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat data dummy untuk boards, cards, assignments, dan comments
     */
    public function run(): void
    {
        // Ambil beberapa project yang sudah ada
        $projects = Project::with('members.user')->take(3)->get();
        
        if ($projects->isEmpty()) {
            $this->command->info('No projects found. Please run ProjectSeeder first.');
            return;
        }

        foreach ($projects as $project) {
            // Buat 2-3 boards per project
            $boardsData = [
                [
                    'board_name' => 'Development Sprint',
                    'description' => 'Current development tasks and features',
                ],
                [
                    'board_name' => 'Bug Fixes',
                    'description' => 'Bug tracking and resolution tasks',
                ],
                [
                    'board_name' => 'Feature Requests',
                    'description' => 'New feature development board',
                ]
            ];

            foreach ($boardsData as $index => $boardData) {
                $board = Board::create([
                    'project_id' => $project->id,
                    'board_name' => $boardData['board_name'],
                    'description' => $boardData['description'],
                    'position' => $index + 1,
                ]);

                // Buat cards untuk setiap board
                $this->createCardsForBoard($board, $project);
            }
        }

        $this->command->info('BoardCardSeeder completed successfully!');
    }

    private function createCardsForBoard(Board $board, Project $project)
    {
        $statuses = ['todo', 'in progress', 'review', 'done'];
        $priorities = ['low', 'medium', 'high'];
        
        $cardsData = [
            [
                'card_title' => 'Design user authentication flow',
                'description' => 'Create wireframes and mockups for login, register, and password reset pages. Include error states and success messages.',
                'priority' => 'high',
                'estimated_hours' => 8,
                'actual_hours' => 3.5,
            ],
            [
                'card_title' => 'Implement API endpoints',
                'description' => 'Develop REST API endpoints for user management, project CRUD operations, and board functionality.',
                'priority' => 'high',
                'estimated_hours' => 12,
                'actual_hours' => 8,
            ],
            [
                'card_title' => 'Setup database migrations',
                'description' => 'Create database schema for projects, boards, cards, and user relationships.',
                'priority' => 'medium',
                'estimated_hours' => 4,
                'actual_hours' => 4,
            ],
            [
                'card_title' => 'Frontend component development',
                'description' => 'Build reusable Vue.js components for kanban board, card management, and user interface elements.',
                'priority' => 'medium',
                'estimated_hours' => 16,
                'actual_hours' => 10,
            ],
            [
                'card_title' => 'Write unit tests',
                'description' => 'Create comprehensive test coverage for API endpoints and frontend components.',
                'priority' => 'medium',
                'estimated_hours' => 6,
                'actual_hours' => 0,
            ],
            [
                'card_title' => 'Performance optimization',
                'description' => 'Optimize database queries, implement caching, and improve page load times.',
                'priority' => 'low',
                'estimated_hours' => 8,
                'actual_hours' => 0,
            ],
        ];

        foreach ($cardsData as $index => $cardData) {
            // Assign random status based on position (earlier cards more likely to be done)
            if ($index < 2) {
                $status = 'done';
            } elseif ($index < 4) {
                $status = fake()->randomElement(['in progress', 'review']);
            } else {
                $status = 'todo';
            }

            $card = Card::create([
                'board_id' => $board->id,
                'card_title' => $cardData['card_title'],
                'description' => $cardData['description'],
                'position' => $index + 1,
                'created_by' => $project->created_by,
                'due_date' => fake()->dateTimeBetween('now', '+30 days'),
                'status' => $status,
                'priority' => $cardData['priority'],
                'estimated_hours' => $cardData['estimated_hours'],
                'actual_hours' => $cardData['actual_hours'],
            ]);

            // Assign cards to project members
            if ($project->members->isNotEmpty()) {
                $assignedMembers = $project->members->random(min(2, $project->members->count()));
                
                foreach ($assignedMembers as $member) {
                    CardAssignment::create([
                        'card_id' => $card->id,
                        'user_id' => $member->user_id,
                        'assignment_status' => fake()->randomElement(['assigned', 'in progress', 'completed']),
                    ]);
                }
            }

            // Add some comments
            $this->createCommentsForCard($card, $project);
        }
    }

    private function createCommentsForCard(Card $card, Project $project)
    {
        $commentCount = fake()->numberBetween(0, 4);
        
        if ($commentCount === 0) return;

        // Get all users involved in the project (creator + members)
        $involvedUsers = collect([$project->created_by]);
        if ($project->members->isNotEmpty()) {
            $involvedUsers = $involvedUsers->merge($project->members->pluck('user_id'));
        }
        
        $commentsData = [
            'Looks good! Let me know if you need any help with the implementation.',
            'I\'ve reviewed the requirements and they seem clear. Should we schedule a meeting to discuss the approach?',
            'Great progress so far. The design mockups are exactly what we need.',
            'I found a few edge cases we should consider. Let me add them to the description.',
            'This is ready for testing. I\'ll move it to review once the unit tests are complete.',
            'The API documentation needs to be updated to reflect these changes.',
            'Excellent work! The performance improvements are significant.',
            'We should consider adding error handling for this scenario.',
        ];

        // Skip comments for now since subtask_id is required
        // We'll create comments via direct DB insert or fix the migration
        // for ($i = 0; $i < $commentCount; $i++) {
        //     Comment::create([
        //         'card_id' => $card->id,
        //         'user_id' => $involvedUsers->random(),
        //         'comment_text' => fake()->randomElement($commentsData),
        //         'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        //     ]);
        // }
    }
}
