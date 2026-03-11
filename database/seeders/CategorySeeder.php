<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([            
                    [
                        'title' => "Летчик",
                        'description' => 'курсы для летчика',
                        'code' => 'pilot'
                    ],
                    [
                        'title' => "Борт инженер",
                        'description' => 'курсы для борт инженера',
                        'code' => 'engineer'
                    ],
                    [
                        'title' => "Инженер АВ",
                        'description' => 'курсы для инженера АВ',
                        'code' => 'av_engineer'
                    ],
                    [
                        'title' => "Инженер АСУ",
                        'description' => 'курсы для инженера АСУ',
                        'code' => 'asu_engineer'
                    ],
                    [
                        'title' => "Штурман",
                        'description' => 'курсы для штурмана',
                        'code' => 'navigator'
                    ],

                ]);
            }
        }
