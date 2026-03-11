<?php

namespace App\Models;

// use App\Traits\HasRolesAndPermissions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory,
        // HasRolesAndPermissions,
        LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'fio',
        'first_name',
        'last_name',
        'patronymic',
        'phonenumber',
        'city',
        'country',
        'avatar',
        'organization',
        'position',
        'rank',
        'spfere',
        'specialization',
        'group_id',
        'role',
        'is_active',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class);
    // }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permissions_users');
    }

    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->last_name} {$this->first_name} {$this->patronymic}");
    }

    public function getInitialsAttribute()
    {
        return mb_substr($this->last_name, 0, 1).mb_substr($this->first_name, 0, 1);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->where('name', $permission)->exists() ||
               $this->roles()->whereHas('permissions', function ($query) use ($permission) {
                   $query->where('name', $permission);
               })->exists();
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function courses()
    {
        return $this->hasManyThrough(Course::class, Group::class);
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }

    // связь многие ко многим с таблицей категорий
    //    public function categories()
    //    {
    //        return $this->belongsToMany(Category::class);
    //    }

    // public function courses()
    // {
    //     return $this->hasManyThrough(Course::class,Category::class);
    // }

    // ----------------------------------------------
    // из связанной таблицы Role (если) для текущего пользователя проверить есть ли имя роли равное инструктору или администратору
    public function isAdmin()
    {
        return $this->role === 'Администратор';
    }

    public function isInstructor()
    {
        return $this->role === 'Инструктор';
    }

    public function isStudent()
    {
        return $this->role === 'Обучаемый';
    }

    // ----------------------------------------------
    public function getAllPermissionsAttribute()
    {
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if ($this->user()->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }

        return $permissions;
    }
}
