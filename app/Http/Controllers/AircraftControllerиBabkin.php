<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Models\Aircraft;
use App\Models\Aukstructure;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Link;


class AircraftController extends Controller
{
  public function showclassesfs()
  {

    $courses_path = Config::get('app.courses_path');
    //$courses_path = Config::get('app.private_path');        
    $classes = array();
    if (file_exists($courses_path)) {
      $classes = array_diff(scandir($courses_path), array('..', '.'));
    }
    return array_values($classes);
  }

  public function showauks(string $air)

  {

    $courses_path = Config::get('app.courses_path');

    $full_path = $courses_path . '/' . $air;
    $auks = array();
    if (file_exists($full_path)) {
      $auks = array_diff(scandir($full_path), array('..', '.'));
    }
    return $auks;
  }

  public function indexclasses()
  {
    $aircrafts = Aircraft::orderBy('id')->get();
    foreach ($aircrafts as $_aircrafts)
      $_aircrafts->courses;
    //$aircrafts = DB::table('aircrafts')->orderBy('id')->get('title');        
    return $aircrafts;
  }



  //
  // дообавление классов (Ил-76,Ми-38, etc...)
  //


  public function storeclasses(Request $request)
  {
    //if (DB::table('aircrafts')->where('path', (string)request('path'))->exists()) return; // выбрасываем если такой класс уже существует                          
    $aircraft = new Aircraft();
    $aircraft->title = request('title');
    $aircraft->path = request('path');
    $aircraft->save();

    $aircraft_id = $aircraft->id;

    $coursesArr = array_values($this->showauks($aircraft->path));

    // Добавление записей
    $_coursesArr = [];
    foreach ($coursesArr as $item) 
    { 
      $auk = new Course();
      //$auk->path = $aircraft->path . '/' . $item; // запись вида Ми-38/АУК-01
      $auk->path =  $item; // запись вида АУК-01
      $full_path_manifest = Config::get('app.courses_path') . '/' . $aircraft->path . '/' . $item . '/' . 'imsmanifest.xml';
      //$full_path_manifest = Config::get('app.private_path') . '/' .$aircraft->path.'/'. $item.'/' . 'imsmanifest.xml';
      //--------------privatemanicontroller insert data from manifest new---------------
      // пока оставляем как есть, для фасада storage пути другие !
      $pathmanifest = "private/{$aircraft->path}/{$item}/imsmanifest.xml";
      //dd($pathmanifest);

      if (Storage::exists($pathmanifest)) {
        $contents = Storage::get($pathmanifest);
        // сюда вставляем функцию парсинга xml файла, $contents - string      
        //$menuxmlcontent = $this->parsemanifest($contents, $aircraft_id, $item); // объект                                    
      }


      //--------------end privatemanicontroller insert data from manifest new---------------
      //    $auk->title = $this->get_title_from_manifest($full_path_manifest);
      //    $auk->short_description = "добавленный курс для" . ' ' . $aircraft->path;
      //    $auk->long_description = $this->generateRandomString();
      //    $auk->aircraft_id = $aircraft->id;

      //    $_coursesArr[] = $auk->attributesToArray();
    }
    //Course::insert($_coursesArr);

    return $aircraft;
  }

  public function parsemanifest($contents, $aircraft_id, $auk)
  {
    $curAir = Aircraft::findOrFail($aircraft_id);
    $xml = simplexml_load_string($contents);
    $categories = []; // пишем категории в БД
    $resources = []; // вспомогательный массив ресурсы,для поиска и последующей записи файлов от модуля    
    

    // заполнение категорий в БД
    foreach ($xml->course->category as $key => $c) {

      $cat_id = Category::insertGetId([
        'aircraft_id' => $aircraft_id,
        'title' => (string)$c->attributes()['name'],
        'code' => (string)$c->attributes()['shortname'],
        'description' => $curAir->path . '  ' . 'категории для класса',
      ]);
      $categories[(string)$c->attributes()['shortname']] = $cat_id;
    }    
    //--------------------------------------конец заполнения категорий-------

    //--------------------------------------заполнение ресурсов-------
    foreach ($xml->resources->resource as $key => $c) {
      $temp = [];
      foreach ($c->file as $cc) {
        $filename = (string)$cc->attributes()['href'];
        $ext = substr($filename, -4);
        if ($ext == 'html') {
          $temp[] = (string)$cc->attributes()['href'];
        }
      }
      $resources[(string)$c->attributes()['identifier']] = $temp;
    }
    //dd($resources['DinamikaEDoc-A-00-01-00-00A-010A-A-T40C']);
    //return $resources;
    //--------------------------------------конец заполнения ресурсов-------

    //--------------------------------------заполнение курсов-------
    $course_id  = Course::insertGetId([
      'title' => $xml->organizations->organization->title,
      'aircraft_id' => $aircraft_id, // потом сюда будем передавать реальный aircraft_id
      'short_description' => 'тестовый short_description',
      'long_description' => 'тестовый long_description',
      'path' => $auk
    ]);
    $startnode = $xml->organizations->organization;
    $auktype = 1;
    $parent_id = 0;
    // $course_id = $curAuk->id; // пока временно  
    $this->RecurseXML($startnode, $auktype, $parent_id, $course_id, $resources, $categories);
    //--------------------------------------конец заполнения курсов-------
  }




  //--------------------------------------------------------------------------------
  //-----------------------мой вариант рекурсии-------------------------------------
  //--------------------------------------------------------------------------------
  public function RecurseXML($xml, $auktype, $parent_id, $course_id, $resources, $attrs_ident = '', $attrs_cat = '')
  {

    $child_count = 0;

    foreach ($xml as $key => $value) {

      if ($value && $value->attributes()['categories'] && $value->attributes()['identifierref']) {
        $attrs_ident = ((string)$value->attributes()['identifierref']);
        $attrs_cat = (string)($value->attributes()['categories']);
      }
      $child_count++;

      if ($this->RecurseXML($value, $auktype, $parent_id, $course_id, $resources, $attrs_ident, $attrs_cat) == 0) // не осталось детей

      {
        $description = ['0' => 'название', '1' => 'тема', '2' => 'раздел', '3' => 'модуль'];
        $el = Aukstructure::updateOrCreate(
          [
            'title' => (string)$value,
            'parent_id' => (int)$parent_id,
            'course_id' => (int)$course_id,
          ],
          [
            'type' => $auktype,
            'description' => $description[$auktype],
            'categories' => $attrs_cat,
            'identifier' => $attrs_ident           
          ]
        );
        $parent_id = $el->id;

        if ($auktype == 3 && $resources[$attrs_ident]) //для данного xml где 3-х уровневая и линки только на 3-м уровне
        {
          foreach ($resources[$attrs_ident] as $_links) {
            Link::updateOrCreate(
              [
                'link' => (string)$_links,
                'aukstructure_id' => (int)$parent_id
              ],
            );
          }
        }


        if ($attrs_cat) {
          $curCat = explode(",", $attrs_cat);

          foreach ($curCat as $_curCat) {

            $curCatId = (Category::where('code', 'like', '%' . $_curCat . '%')
              ->get()
              ->pluck('id')
              ->all()
            );

            DB::table('aukstructure_category')->updateOrInsert(
              [
                'category_id' => (int)implode('', $curCatId),
                'aukstructure_id' => (int)$parent_id,
              ],
            );
          }
        }

        $auktype++;
      }
    }
    return $child_count;
  }

//----------------------------------------------------------------------------------
//-----------------------конец вариант рекурсии-------------------------------------
//----------------------------------------------------------------------------------





  // public function RecurseXML($xml, $auktype, $parent_id, $course_id, $resources, $categories, $attrs_ident = '', $attrs_cat = '')
  // {

  //   $child_count = 0; /////??????
  //   $description = ['0' => 'название', '1' => 'тема', '2' => 'раздел', '3' => 'модуль'];
    

  //   foreach ($xml->children()->item as $key => $value) {
      
  //     $attrs_ident = (string)$value->attributes()['identifierref'];

  //     $el_id = Aukstructure::insertGetId([
  //        'title' => (string)$value->title,
  //        'parent_id' => (int)$parent_id,
  //        'course_id' => (int)$course_id,
  //        'type' => $auktype,
  //        'description' => $description[$auktype],

  //       // 'categories' => $attrs_cat,
  //       'identifier' => $attrs_ident,
  //       //'identifier' => $resources[$key->attributes()['identifier']]
  //     ]);
      

  //      $tmp_categories = explode(',', $value->attributes()['categories']);
      
  //     foreach ($tmp_categories as $category) {
  //       DB::table('aukstructure_category')->insert(
  //         [
  //           'category_id' => (int)$categories[$category],
  //           'aukstructure_id' => (int)$el_id,
  //         ],
  //       );
  //     }

  //     if ($auktype == 3 && $resources[$attrs_ident]) //для данного xml где 3-х уровневая и линки только на 3-м уровне
  //     {
  //       foreach ($resources[$attrs_ident] as $_links) {
  //         Link::insert(
  //           [
  //             'link' => (string)$_links,
  //             'aukstructure_id' => (int)$el_id
  //           ],
  //         );
  //       }
  //     }

  //     foreach ($xml->children() as $child_key => $child) {        
  //       if (strcmp($child_key, 'item') == 0) 
  //       {
  //         $this->RecurseXML($child, $auktype + 1, $el_id, $course_id, $resources, $categories); //, $attrs_ident, $attrs_cat);
  //       }       
  //     }
  //   }
  //   return $child_count;
  // }




  // ------------парсинг xml из privateManiController------------
  //     public function parsemanifest_old($contents)
  //     {
  //         $xml = simplexml_load_string($contents);
  //         $resources = [];
  //         $data = [];
  //         //------------------ресурсы
  //         foreach ($xml->resources->resource as $key => $c) {
  //             $temp = [];
  //             foreach ($c->file as $cc) {
  //                 array_push($temp, (string)$cc->attributes()['href']);                
  //             }
  //             $temp[]['ident']=(string)$c->attributes()['identifier'];   
  //             array_push($resources, json_encode($temp, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

  //         }
  //         //-----------------------------------------------
  //         //-------------------категории-------------
  //         $categories = [];
  //         $data = [];
  //         foreach ($xml->course->category as $key => $c) {
  //             $data['name'] = (string)$c->attributes()['name'];
  //             $data['shortname'] = (string)$c->attributes()['shortname'];
  //             array_push($categories, $data);
  //         }
  // //-------------------title-------------
  //         $title = [];
  //         foreach ($xml->organizations->organization->item->item->item as $key => $c) {
  //             //array_push($title,(string)$c->attributes()['identifierref']);
  //             //array_push($title,(string)$c->title);
  //             $t = [];
  //             $v=(string)$c->attributes()['identifierref'];
  //             $k = (string)$c->title;
  //             //$t[(string)$c->attributes()['identifierref']] = (string)$c->title;
  //             $t[$k]=$v;  //поменяем местами ключ и значение для более простого поиска
  //             array_push($title, $t);
  //         }
  // //-------------------title-------------
  //         $response = [
  //             'title' => ($xml->course)["title"], // старый вариант title
  //             'categories' => $categories,
  //             'resources' => $resources,
  //             'titles' => $title //array title modules
  //         ];
  //         return response($response, 201);
  //         //return $response;
  //     }

  // ------------конец парсинг xml из privateManiController------------





  //
  //------------тесты для чтения xml node---------
  //
  // public function indexclasses11()
  // {
  //     $item = 'АУК-02';
  //     $aircraft = 'Ил-76';
  //     $manifestpath = Config::get('app.courses_path').'/'.$aircraft.'/'. $item.'/'.'imsmanifest.xml';
  //     $xml=simplexml_load_file($manifestpath) or die("Error: Cannot create object");

  //     return $xml->organizations->organization->title;        
  // }

  // public function generateRandomString($length = 5)
  // {
  //   return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
  // }
  // public function get_title_from_manifest(string $manifestpath)
  // {
  //   $xml = simplexml_load_file($manifestpath) or die("Error: Cannot create object");
  //   return $xml->organizations->organization->title;
  // }


}
