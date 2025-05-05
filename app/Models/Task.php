<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'order',
        'board_id'
    ];

    protected $casts = [
        'order' => 'integer',
        'board_id' => 'integer',
    ];

    /**
     * Get the board that owns the task.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }
} 