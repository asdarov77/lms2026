<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
//use App\Models\Category;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('courses')->insert([            
                    [
                        'title' => "Подготовка и выполнение полета. Боевое применение",
                        'short_description' => 'курсы для летчика',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Эксплуатация оборудования",
                        'short_description' => 'курсы для борт инженера',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Авиационное вооружение и десантно-транспортное оборудование",
                        'short_description' => 'курсы для инженера АВ',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Подготовка к полетам самолета, самолетных систем и силовой установки",
                        'short_description' => 'курсы для бортового техника',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Авиационное оборудование",
                        'short_description' => 'курсы для АО',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Планер самолета",
                        'short_description' => 'курсы планера самолета',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Силовая установка",
                        'short_description' => 'курсы двигатель',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Силовая установка",
                        'short_description' => 'курсы двигатель',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Конструкция вертолета и его системы. Описание и эксплуатация",
                        'short_description' => 'описание вертолета..',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Практическая аэродинамика",
                        'short_description' => 'курсы аэродинамика..',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Летная эксплуатация (раздел 4 Летные данные)",
                        'short_description' => 'курсы ЛЭ',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Конструкция вертолета и его системы. Техническое обслуживание",
                        'short_description' => 'курсы ТО',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Конструкция силовой установки. Техническое обслуживание",
                        'short_description' => 'курсы ТО',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Радиоэлектронное оборудование. Техническое обслуживание",
                        'short_description' => 'курсы РЭО',
                        'long_description' => '',
                    ],
                    [
                        'title' => "Аварийно-спасательные средства. Техническое описание",
                        'short_description' => 'курсы РЭО',
                        'long_description' => '',
                    ],
                    
                    

                ]);
            }
        }
