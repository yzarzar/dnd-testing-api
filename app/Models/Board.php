<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the tasks for the board.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
