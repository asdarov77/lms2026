<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class AssignmentSubmission extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'score',
        'feedback',
        'status',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 