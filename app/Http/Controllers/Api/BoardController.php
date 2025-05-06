<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BoardController extends Controller
{
    /**
     * Display a listing of the boards.
     */
    public function index(): JsonResponse
    {
        $boards = Board::all();
        return response()->json($boards);
    }

    /**
     * Store a newly created board.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board = Board::create($validated);
        return response()->json($board, 201);
    }

    /**
     * Display the specified board with its columns and tasks.
     */
    public function show(Board $board): JsonResponse
    {
        $board->load(['columns.tasks']);
        return response()->json($board);
    }

    /**
     * Update the specified board.
     */
    public function update(Request $request, Board $board): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board->update($validated);
        return response()->json($board);
    }

    /**
     * Remove the specified board.
     */
    public function destroy(Board $board): JsonResponse
    {
        $board->delete();
        return response()->json(null, 204);
    }
} 