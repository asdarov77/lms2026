<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'user_id',
        'related_id',
        'related_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that created the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (Course, Assignment, Group, etc.).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include activities related to a specific model.
     */
    public function scopeRelatedTo($query, $model)
    {
        return $query->where('related_type', get_class($model))
                    ->where('related_id', $model->id);
    }

    /**
     * Create a new activity record.
     */
    public static function log($title, $description, $type, $user = null, $related = null)
    {
        return static::create([
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'user_id' => $user ? $user->id : null,
            'related_id' => $related ? $related->id : null,
            'related_type' => $related ? get_class($related) : null,
        ]);
    }
} 