<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Test User',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'fio' => 'Test User',
        ]);
    }

    public function test_user_has_fillable_attributes(): void
    {
        $user = new User;

        $fillable = $user->getFillable();

        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('fio', $fillable);
    }

    public function test_user_can_have_group(): void
    {
        $group = Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'is_active' => true,
        ]);

        $user = User::create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Test User',
            'group_id' => $group->id,
        ]);

        $this->assertEquals($group->id, $user->group->id);
    }

    public function test_user_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Admin User',
            'role' => 'Администратор',
            'is_active' => 1,
        ]);

        $this->assertTrue($user->isAdmin());
    }

    public function test_user_is_admin_returns_false_for_non_admin(): void
    {
        $user = User::create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Regular User',
            'role' => 'Обучаемый',
            'is_active' => 1,
        ]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_user_is_instructor_returns_true_for_instructor_role(): void
    {
        $user = User::create([
            'email' => 'instructor@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Instructor User',
            'role' => 'Инструктор',
            'is_active' => 1,
        ]);

        $this->assertTrue($user->isInstructor());
    }

    public function test_user_is_student_returns_true_for_student_role(): void
    {
        $user = User::create([
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Student User',
            'role' => 'Обучаемый',
            'is_active' => 1,
        ]);

        $this->assertTrue($user->isStudent());
    }

    public function test_user_scope_active_filters_inactive_users(): void
    {
        User::create([
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Active User',
            'is_active' => true,
        ]);

        User::create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Inactive User',
            'is_active' => false,
        ]);

        $activeUsers = User::active()->get();

        $this->assertEquals(1, $activeUsers->count());
        $this->assertEquals('active@example.com', $activeUsers->first()->email);
    }

    public function test_user_can_have_full_name_attribute(): void
    {
        $user = User::create([
            'email' => 'fullname@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Full Name User',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'patronymic' => 'Smith',
            'is_active' => true,
        ]);

        $this->assertEquals('Doe John Smith', $user->full_name);
    }

    public function test_user_can_have_initials_attribute(): void
    {
        $user = User::create([
            'email' => 'initials@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Initials User',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_active' => true,
        ]);

        $this->assertEquals('DJ', $user->initials);
    }

    public function test_user_settings_are_cast_to_array(): void
    {
        $user = User::create([
            'email' => 'settings@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Settings User',
            'settings' => ['theme' => 'dark', 'notifications' => true],
            'is_active' => true,
        ]);

        $this->assertIsArray($user->settings);
        $this->assertEquals('dark', $user->settings['theme']);
    }
}
