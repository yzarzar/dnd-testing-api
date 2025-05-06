<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    /**
     * Display a listing of columns for a board.
     */
    public function index(Board $board): JsonResponse
    {
        $columns = $board->columns()->with('tasks')->get();
        return response()->json($columns);
    }

    /**
     * Display the specified column with its tasks.
     */
    public function show(Column $column): JsonResponse
    {
        $column->load('tasks');
        return response()->json($column);
    }

    /**
     * Store a newly created column.
     */
    public function store(Request $request, Board $board): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Get the maximum position value
        $maxPosition = $board->columns()->max('position') ?? -1;
        
        $column = $board->columns()->create([
            'title' => $validated['title'],
            'position' => $maxPosition + 1,
        ]);

        return response()->json($column, 201);
    }

    /**
     * Update the specified column.
     */
    public function update(Request $request, Column $column): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $column->update($validated);
        return response()->json($column);
    }

    /**
     * Remove the specified column.
     */
    public function destroy(Column $column): JsonResponse
    {
        // Get board and position information before deletion
        $boardId = $column->board_id;
        $currentPosition = $column->position;
        
        DB::transaction(function () use ($column, $boardId, $currentPosition) {
            // Delete the column
            $column->delete();
            
            // Update positions of subsequent columns (decrement their positions)
            Column::where('board_id', $boardId)
                ->where('position', '>', $currentPosition)
                ->decrement('position');
        });
        
        return response()->json(null, 204);
    }

    /**
     * Update column positions.
     * This now handles proper drag-and-drop reordering like professional management platforms.
     */
    public function updatePositions(Request $request, Board $board): JsonResponse
    {
        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:columns,id',
            'columns.*.position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $board) {
            foreach ($validated['columns'] as $columnData) {
                $column = Column::where('id', $columnData['id'])->first();
                
                if ($column && $column->board_id == $board->id) {
                    $oldPosition = $column->position;
                    $newPosition = $columnData['position'];
                    
                    // Skip if position didn't change
                    if ($oldPosition == $newPosition) {
                        continue;
                    }
                    
                    // Moving left (decreasing position)
                    if ($oldPosition > $newPosition) {
                        // Shift columns right to make room (increment positions)
                        Column::where('board_id', $board->id)
                            ->where('position', '>=', $newPosition)
                            ->where('position', '<', $oldPosition)
                            ->increment('position');
                    } 
                    // Moving right (increasing position)
                    else if ($oldPosition < $newPosition) {
                        // Shift columns left (decrement positions)
                        Column::where('board_id', $board->id)
                            ->where('position', '>', $oldPosition)
                            ->where('position', '<=', $newPosition)
                            ->decrement('position');
                    }
                    
                    // Update the column's position
                    $column->update(['position' => $newPosition]);
                }
            }
        });

        // Return columns in their new order
        $updatedColumns = $board->columns()->with('tasks')->get();
        return response()->json($updatedColumns);
    }

    /**
     * Move a single column (more targeted drag and drop functionality).
     */
    public function moveColumn(Request $request, Column $column): JsonResponse
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
        ]);

        $boardId = $column->board_id;
        $oldPosition = $column->position;
        $newPosition = $validated['position'];

        // Skip if position didn't change
        if ($oldPosition == $newPosition) {
            return response()->json($column);
        }

        DB::transaction(function () use ($column, $boardId, $oldPosition, $newPosition) {
            // Moving left (decreasing position)
            if ($oldPosition > $newPosition) {
                // Shift columns right to make room (increment positions)
                Column::where('board_id', $boardId)
                    ->where('position', '>=', $newPosition)
                    ->where('position', '<', $oldPosition)
                    ->increment('position');
            } 
            // Moving right (increasing position)
            else {
                // Shift columns left (decrement positions)
                Column::where('board_id', $boardId)
                    ->where('position', '>', $oldPosition)
                    ->where('position', '<=', $newPosition)
                    ->decrement('position');
            }
            
            // Update the column's position
            $column->update(['position' => $newPosition]);
        });

        // Load and return the updated column
        $column->refresh();
        $column->load('tasks');
        return response()->json($column);
    }
} 