<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run()
    {
        $groups = [
            [
                'groupname' => 'Группа 1',
                'groupdescription' => 'Основная группа',
            ],
            [
                'groupname' => 'Группа 2',
                'groupdescription' => 'Вторая группа',
            ],
            [
                'groupname' => 'Группа 3',
                'groupdescription' => 'Третья группа',
            ],
        ];

        foreach ($groups as $group) {
            Group::updateOrCreate(
                ['groupname' => $group['groupname']],
                $group
            );
        }
    }
}
