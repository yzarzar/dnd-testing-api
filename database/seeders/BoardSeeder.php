<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default board
        $board = Board::create([
            'name' => 'My First Board'
        ]);

        // Create some sample tasks for the board
        Task::create([
            'board_id' => $board->id,
            'title' => 'Create project repository',
            'description' => 'Set up Git repo and initial structure',
            'status' => 'todo',
            'order' => 0
        ]);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Design database schema',
            'description' => 'Create tables and relationships',
            'status' => 'todo',
            'order' => 1
        ]);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Setup CI/CD pipeline',
            'description' => 'Configure GitHub Actions for continuous integration',
            'status' => 'in-progress',
            'order' => 0
        ]);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Write project documentation',
            'description' => 'Create README and API documentation',
            'status' => 'done',
            'order' => 0
        ]);
    }
}
