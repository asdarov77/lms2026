<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\User;
use App\Traits\LogsActivity;

class Group extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'groupname',
        'groupdescription',
        'is_active',
        'created_by',
        'settings',
        'max_users',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMemberCountAttribute()
    {
        return $this->users()->count();
    }

    public function isFull()
    {
        return $this->max_users && $this->member_count >= $this->max_users;
    }

    public function canAddUser()
    {
        return !$this->max_users || $this->member_count < $this->max_users;
    }

    public function group2learnings() {
        return $this->hasMany(Group2learning::class);
    }
}
