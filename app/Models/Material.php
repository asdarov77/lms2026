<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Material extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'content',
        'topic_id',
        'order',
        'type',
        'file_path',
        'status',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
} 