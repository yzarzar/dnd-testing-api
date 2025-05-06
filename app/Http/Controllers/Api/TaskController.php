<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a column.
     */
    public function index(Column $column): JsonResponse
    {
        $tasks = $column->tasks()->get();
        return response()->json($tasks);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json($task);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request, Column $column): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tag' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|string|max:255',
            'priority' => 'nullable|string|in:low,medium,high',
        ]);

        // Get the maximum position value
        $maxPosition = $column->tasks()->max('position') ?? -1;
        
        $validated['position'] = $maxPosition + 1;
        $task = $column->tasks()->create($validated);

        return response()->json($task, 201);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tag' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|string|max:255',
            'priority' => 'nullable|string|in:low,medium,high',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Get column and position information before deletion
        $columnId = $task->column_id;
        $currentPosition = $task->position;
        
        DB::transaction(function () use ($task, $columnId, $currentPosition) {
            // Delete the task
            $task->delete();
            
            // Update positions of subsequent tasks (decrement their positions)
            Task::where('column_id', $columnId)
                ->where('position', '>', $currentPosition)
                ->decrement('position');
        });
        
        return response()->json(null, 204);
    }

    /**
     * Move a task (drag and drop functionality).
     * This handles both column changes and position changes with professional reordering.
     */
    public function move(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'position' => 'required|integer|min:0',
        ]);

        $result = DB::transaction(function () use ($task, $validated) {
            $oldColumnId = $task->column_id;
            $newColumnId = $validated['column_id'];
            $oldPosition = $task->position;
            $newPosition = $validated['position'];

            // If no actual change, return early
            if ($oldColumnId == $newColumnId && $oldPosition == $newPosition) {
                return $task;
            }

            // If moving to a different column
            if ($oldColumnId != $newColumnId) {
                // Update positions in the old column - decrement positions of tasks that were after the moved task
                Task::where('column_id', $oldColumnId)
                    ->where('position', '>', $oldPosition)
                    ->decrement('position');

                // Update positions in the new column - increment positions of tasks that will be after the moved task
                Task::where('column_id', $newColumnId)
                    ->where('position', '>=', $newPosition)
                    ->increment('position');
            } else {
                // Moving within the same column
                if ($oldPosition < $newPosition) {
                    // Moving down - decrement positions of tasks that are between old and new positions
                    Task::where('column_id', $oldColumnId)
                        ->where('position', '>', $oldPosition)
                        ->where('position', '<=', $newPosition)
                        ->decrement('position');
                } else if ($oldPosition > $newPosition) {
                    // Moving up - increment positions of tasks that are between new and old positions
                    Task::where('column_id', $oldColumnId)
                        ->where('position', '<', $oldPosition)
                        ->where('position', '>=', $newPosition)
                        ->increment('position');
                }
            }

            // Update the task with the new column and position
            $task->update([
                'column_id' => $newColumnId,
                'position' => $newPosition,
            ]);

            // Get the affected columns for the response
            $affectedColumns = collect([$oldColumnId, $newColumnId])->unique();
            return Column::whereIn('id', $affectedColumns)->with('tasks')->get();
        });

        return response()->json([
            'message' => 'Task moved successfully',
            'affected_columns' => $result
        ]);
    }

    /**
     * Update multiple task positions within a column (bulk reordering).
     */
    public function updatePositions(Request $request, Column $column): JsonResponse
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $column) {
            foreach ($validated['tasks'] as $taskData) {
                $task = Task::where('id', $taskData['id'])->first();
                
                if ($task && $task->column_id == $column->id) {
                    $oldPosition = $task->position;
                    $newPosition = $taskData['position'];
                    
                    // Skip if position didn't change
                    if ($oldPosition == $newPosition) {
                        continue;
                    }
                    
                    // Moving up (decreasing position)
                    if ($oldPosition > $newPosition) {
                        // Shift tasks down to make room (increment positions)
                        Task::where('column_id', $column->id)
                            ->where('position', '>=', $newPosition)
                            ->where('position', '<', $oldPosition)
                            ->increment('position');
                    } 
                    // Moving down (increasing position)
                    else if ($oldPosition < $newPosition) {
                        // Shift tasks up (decrement positions)
                        Task::where('column_id', $column->id)
                            ->where('position', '>', $oldPosition)
                            ->where('position', '<=', $newPosition)
                            ->decrement('position');
                    }
                    
                    // Update the task's position
                    $task->update(['position' => $newPosition]);
                }
            }
        });

        // Return tasks in their new order
        $updatedTasks = $column->tasks()->get();
        return response()->json($updatedTasks);
    }
} 