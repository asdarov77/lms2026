<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Aukstructure;
use App\Models\Course;

class CourseFilter extends AbstractFilter
{
    public const TITLE = 'title';
    public const PATH = 'path';
    public const AIRCRAFT_ID = 'aircraft_id';
    public const CATEGORY_ID = 'category_id';        
    public const PARENT_ID = 'parent_id';
    public const TYPE_ID = 'type_id';
    public const COURSE_ID = 'course_id';


    protected function getCallbacks(): array
    {
        return [
            self::TITLE => [$this, 'title'],
            self::PATH => [$this, 'path'],
            self::AIRCRAFT_ID => [$this, 'aircraftId'],
            self::CATEGORY_ID => [$this, 'categoryId'],            
            self::PARENT_ID => [$this, 'parentId'],
            self::TYPE_ID => [$this, 'typeId'],
            self::COURSE_ID => [$this, 'courseId']

        ];
    }
    //-------рабочий--------------------------
//     public function catWcor(Builder $builder, $cat_id, $cor_id)
//     {       
//  {
//      $builder->whereHas('categories', function ($query) use ($cat_id) {
//          $query->where('categories.id', '=', $cat_id);
//      })->where('id', '=', $cor_id);

//      $builder->with(['aukstructures', 'categories']);
//  }
//     }

    public function title(Builder $builder, $value)
    {
        $builder->where('title', 'like', "%{$value}%");
    }
    public function path(Builder $builder, $value)
    {
        $builder->where('path', 'like', "%{$value}%");
    }

    public function aircraftId(Builder $builder, $value)
    {
        $builder->where('aircraft_id', $value);
    }

    // public function categoryId(Builder $builder, $value)
    // {

    //     $builder->withWhereHas('categories', function ($q) use ($value) {

    //         $q->where("category_id", $value); // берет из pivot таблицы category-course
    //     })->withWhereHas('aukstructures', 
    //     function ($qq) {
    //     //    $qq->where("type", "!=",0);        
    //     });
    //     // $builder->whereHas('categories', function ($q) use ($value) {
    //     //     $q->where('category_id', $value);
    //     // })
    //     // ->whereHas('aukstructures', function ($qq) use ($value) {
    //     //     $qq->where('category_id', $value);
    //     // })
    //     ;
    // }

    // public function categoryId(Builder $builder, $categoryId)
    // {
    //     // // Фильтруем коллекцию по category_id из categories
    //     // -------------------работает------------------------------------
    //     $builder->withWhereHas('categories', function ($query) use ($categoryId) {
    //         $query->where('categories.id', '=', $categoryId);
    //     });
    //         // Фильтруем коллекцию по category_id из categories
    // // Фильтруем коллекцию по category_id из categories
    // // $builder->whereHas('categories', function ($query) use ($categoryId) {
    // //     $query->where('id', '=', $categoryId);
    // // });
    // // $builder->withWhereHas('aukstructures', function ($query) {
    // //     $query->where('categories', 'like', 'OB');
    // // });
    // // Загружаем все связанные aukstructure и categories
    // $builder->with(['aukstructures', 'categories']);
    // //$builder->whereHas('aukstructures',function ($query){ $query->where('aukstructures.categories', 'like', "OB"); });
        
    //     // -------------------работает------------------------------------


    //     // Фильтруем коллекцию по category_id из categories и возвращаем только необходимые поля 'id' и 'title' из таблицы 'aukstructures'
    //     // $builder->with(['aukstructures' => function ($query) use ($categoryId) {
    //     //     return $query->whereHas('categories', function ($query) use ($categoryId) {
    //     //         $query->where('categories.id', '=', $categoryId);
    //     //     });
    //     // }]);
    //     // $request = request();
    //     // if ($request->has('category_id')) {
    //     //     $categoryId = $request->query('category_id');
    //     //     $builder->with(['aukstructures' => function ($query) use ($categoryId) {
    //     //         $query->whereHas('categories', function ($query) use ($categoryId) {
    //     //             $query->where('categories.id', '=', $categoryId);
    //     //         });
    //     //     }]);
    //     // }

    //     // return $builder;


    // }



    public function categoryId(Builder $builder, $categoryId)
{
    $builder->whereHas('categories', function ($query) use ($categoryId) {
        $query->where('categories.id', '=', $categoryId);
    });
    //->where('id', '=', $courseId);

    $builder->with(['aukstructures', 'categories']);
}

// public function categoryId(Builder $builder, $categoryId, $courseId)
// {
//     $builder->whereHas('categories', function ($query) use ($categoryId) {
//         $query->where('categories.id', '=', $categoryId);
//     })->where('id', '=', $courseId);

//     $builder->with(['aukstructures', 'categories']);
// }

    public function parentId(Builder $builder, $value)
    {
        $builder->withWhereHas('aukstructures', function ($q) use ($value) {
            $q->where("parent_id", $value);
        });
    }

    public function courseId(Builder $builder, $value)
    {


        // Ищем курс с указанным id
        $course = Course::where('id', $value)->with('aukstructures')->first();

        if ($course) {
            // Если курс найден, то возвращаем его
            $builder->where('id', $course->id);
        } else {
            // Если курс не найден, то ищем по aukstructure.id
            $builder->whereHas('aukstructures', function ($query) use ($value) {
                $query->where('id', $value);
            });
        }    
        // Загружаем все связанные aukstructure categories
        $builder->with(['categories','aukstructures']);
    }


    public function typeId(Builder $builder, $value)
    {
        $builder->withWhereHas("aukstructures", function ($q) use ($value) {
            $q->where("type", $value);
        });
    }


    private function findChildren($value)
{
    $children = [];
    $aukstructures = Aukstructure::where('parent_id', $value)->get();

    foreach ($aukstructures as $aukstructure) {
        $children[] = $aukstructure;
        $children = array_merge($children, $this->findChildren($aukstructure->id));
    }

    return $children;
}


    //
    //---------------------------------мучения
    //
    //     public function courseId(Builder $builder, $value)
    //     {
    //         // чтобы просто получить курс с указанным id        
    //         //
    //         //$builder->where("id", $value);
    //         //
    //         // working !!!
    //         if($value<15){


    //         $builder->where("id", $value)
    //         //->withWhereHas('categories')
    //         ->withWhereHas('aukstructures', function ($query) use ($value) {
    //             $query->where('course_id', $value);
    //         });        
    //     }
    //     else
    //         // end working !!!

    //     // If no course matches the given ID, retrieve all courses that have an aukstructure with the ID
    //     //if (!$builder->exists()) {
    //          {



    //         //   $builder->whereHas('aukstructures', function ($query) use ($value) {
    //         //      $query->where('id', 245);
    //         //  });

    //         // $builder->with('categories')->withWhereHas('aukstructures', function ($query) use ($value) {
    //         //     $query->where('id', $value);
    //         // });


    // //!!
    // $builder
    // ->withWhereHas('aukstructures', function ($query) use ($value) {
    //     $query->where('id', $value);
    // });        

    //         // $builder->whereHas('aukstructures', function ($query) use ($value) {
    //         //     $query->where('aukstructures.id', $value);
    //         // });

    //     //!!
    //         // Получаем всех потомков aukstructure с указанным ID в качестве родителя
    //         //  $builder->where(function ($query) use ($value) {
    //         //      $query->where('id', $value)
    //         //            ->orWhereIn('id', function ($subquery) use ($value) {
    //         //                $subquery->select('descendant')
    //         //                         ->from('aukstructures')
    //         //                         ->where('ancestor', $value);
    //         //            });
    //         //  });

    //         // Загружаем связи aukstructures и categories
    //         //$builder->with(['aukstructures', 'categories']);

    //         // Возвращаем экземпляр построителя запросов

    //     }
    //     }






            // if ($value < 15) {
        //     $builder->where("id", $value)
        //         //->withWhereHas('categories')
        //         ->withWhereHas('aukstructures', function ($query) use ($value) {
        //             $query->where('course_id', $value);
        //         });
        // } else {
        //      $builder
        //          ->withWhereHas('aukstructures', function ($query) use ($value) {
        //              $query->where('id', $value);
        //          });
        //  }
        //-----------------------------------------------
        
        // $builder->where("id", $value)
        // ->withwhereHas('aukstructures', function ($query) use ($value) {
        //     $query->where('id', $value)
        //           ->orWhere('parent_id', $value);
        // });

        // $children = $this->findChildren($value);

        // $ids = collect($children)->pluck('id')->toArray();
        // dd($ids);
        // $ids[] = $value;
    
        //$builder->whereIn("id", $ids);
//         $builder
//         ->withwhereHas('aukstructures', function ($query) use ($value) {
//                  $query->whereIn('id', $value);
//  });
    // Ищем курс с указанным id
    // $course = Course::find($value);

    // if ($course) {
    //     // Если курс найден, то фильтруем по его id
    //     $builder->where('id', $course->id)
    //     ->with('aukstructures');
    // } else {
    //     // Если курс не найден, то ищем по aukstructure.id
    //     $builder->whereHas('aukstructures', function ($query) use ($value) {
    //         $query->where('id', $value);
    //     });
    // }




}
