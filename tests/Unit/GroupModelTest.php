<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_can_be_created(): void
    {
        $group = Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('groups', [
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
        ]);
    }

    public function test_group_has_fillable_attributes(): void
    {
        $group = new Group;

        $fillable = $group->getFillable();

        $this->assertContains('groupname', $fillable);
        $this->assertContains('groupdescription', $fillable);
    }

    public function test_group_can_have_users(): void
    {
        $group = Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'is_active' => true,
        ]);

        $this->assertTrue(method_exists($group, 'users'));
    }

    public function test_group_scope_active_filters_inactive_groups(): void
    {
        Group::create([
            'groupname' => 'Active Group',
            'groupdescription' => 'Active Description',
            'is_active' => true,
        ]);

        Group::create([
            'groupname' => 'Inactive Group',
            'groupdescription' => 'Inactive Description',
            'is_active' => false,
        ]);

        $activeGroups = Group::active()->get();

        $this->assertEquals(1, $activeGroups->count());
        $this->assertEquals('Active Group', $activeGroups->first()->groupname);
    }

    public function test_group_can_have_creator(): void
    {
        $creator = User::create([
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Creator User',
            'is_active' => true,
        ]);

        $group = Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'created_by' => $creator->id,
            'is_active' => true,
        ]);

        $this->assertEquals($creator->id, $group->creator->id);
    }

    public function test_group_can_check_if_full(): void
    {
        $group = Group::create([
            'groupname' => 'Limited Group',
            'groupdescription' => 'Limited Description',
            'max_users' => 2,
            'is_active' => true,
        ]);

        $this->assertFalse($group->isFull());

        User::create([
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'fio' => 'User 1',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        User::create([
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'fio' => 'User 2',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        $group->refresh();

        $this->assertTrue($group->isFull());
    }

    public function test_group_can_check_if_can_add_user(): void
    {
        $group = Group::create([
            'groupname' => 'Test Group',
            'groupdescription' => 'Test Description',
            'max_users' => 1,
            'is_active' => true,
        ]);

        $this->assertTrue($group->canAddUser());

        User::create([
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'fio' => 'User 1',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        $group->refresh();

        $this->assertFalse($group->canAddUser());
    }

    public function test_group_settings_are_cast_to_array(): void
    {
        $group = Group::create([
            'groupname' => 'Settings Group',
            'groupdescription' => 'Settings Description',
            'settings' => ['color' => 'blue', 'notifications' => false],
            'is_active' => true,
        ]);

        $this->assertIsArray($group->settings);
        $this->assertEquals('blue', $group->settings['color']);
    }

    public function test_group_can_get_member_count(): void
    {
        $group = Group::create([
            'groupname' => 'Members Group',
            'groupdescription' => 'Members Description',
            'is_active' => true,
        ]);

        $this->assertEquals(0, $group->member_count);

        User::create([
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'fio' => 'Member User',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        $group->refresh();

        $this->assertEquals(1, $group->member_count);
    }
}
