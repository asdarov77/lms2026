<?php

namespace App\Http\Controllers;

use App\GiftParser\GiftParser;
use App\Models\Aircraft;
use App\Models\Aukstructure;
use App\Models\Category;
use App\Models\Course;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    protected GiftParser $giftParser;

    public function __construct(GiftParser $giftParser)
    {
        $this->giftParser = $giftParser;

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (! $user || (! $user->isAdmin() && ! $user->isInstructor())) {
                return response()->json(['error' => 'Доступ запрещён. Только администратор может импортировать курсы.'], 403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        return response()->json([
            'message' => 'Страница импорта курсов',
            'usage' => [
                'GET /api/import/aircrafts - получить доступные классы',
                'POST /api/import/run - запустить импорт',
                'POST /api/import/clear - очистить базу данных',
            ],
        ]);
    }

    public function getAircrafts()
    {
        $coursesPath = Config::get('app.courses_path');
        $aircrafts = [];

        if (file_exists($coursesPath)) {
            $items = array_diff(scandir($coursesPath), ['..', '.']);
            foreach ($items as $item) {
                if (is_dir($coursesPath.'/'.$item) && $item !== 'private') {
                    $coursesCount = count(array_diff(scandir($coursesPath.'/'.$item), ['..', '.', 'GIFT']));

                    $aircraftInDb = Aircraft::where('path', $item)->first();

                    $aircrafts[] = [
                        'path' => $item,
                        'title' => $aircraftInDb ? $aircraftInDb->title : $item,
                        'courses_count' => $coursesCount,
                        'exists_in_db' => (bool) $aircraftInDb,
                        'courses_imported' => $aircraftInDb ? Course::where('aircraft_id', $aircraftInDb->id)->count() : 0,
                        'id' => $aircraftInDb?->id,
                    ];
                }
            }
        }

        return response()->json($aircrafts);
    }

    public function import(Request $request)
    {
        $request->validate([
            'aircraft_path' => 'required|string',
        ]);

        $aircraftPath = $request->input('aircraft_path');
        $force = $request->boolean('force', false);

        Log::info("Начало импорта курсов для aircraft: {$aircraftPath}");

        $result = [
            'aircraft' => $aircraftPath,
            'courses_created' => 0,
            'categories_created' => 0,
            'aukstructures_created' => 0,
            'questions_imported' => 0,
            'answers_imported' => 0,
            'errors' => [],
        ];

        try {
            $existingAircraft = Aircraft::where('path', $aircraftPath)->first();

            if ($existingAircraft && ! $force) {
                return response()->json([
                    'success' => false,
                    'message' => "Класс '{$aircraftPath}' уже загружен в базу данных. Используйте параметр force=true для перезаписи.",
                    'aircraft_id' => $existingAircraft->id,
                    'courses_count' => Course::where('aircraft_id', $existingAircraft->id)->count(),
                ], 422);
            }

            if ($force && $existingAircraft) {
                Log::info("Принудительный переимпорт класса: {$aircraftPath}");
                Course::where('aircraft_id', $existingAircraft->id)->delete();
                Category::where('aircraft_id', $existingAircraft->id)->delete();
            }

            $aircraft = Aircraft::updateOrCreate(
                ['path' => $aircraftPath],
                ['title' => $aircraftPath]
            );
            Log::info("Создан/обновлен Aircraft: {$aircraftPath}, ID: {$aircraft->id}");

            $coursesPath = Config::get('app.courses_path').'/'.$aircraftPath;

            if (! file_exists($coursesPath)) {
                throw new \Exception("Папка курсов не найдена: {$coursesPath}");
            }

            $courseFolders = array_diff(scandir($coursesPath), ['..', '.', 'GIFT']);

            foreach ($courseFolders as $courseFolder) {
                $fullCoursePath = $coursesPath.'/'.$courseFolder;

                if (! is_dir($fullCoursePath)) {
                    continue;
                }

                $manifestPath = $fullCoursePath.'/imsmanifest.xml';

                if (Storage::exists("{$aircraftPath}/{$courseFolder}/imsmanifest.xml")) {
                    $contents = Storage::get("{$aircraftPath}/{$courseFolder}/imsmanifest.xml");
                    $manifestResult = $this->parseManifest($contents, $aircraft->id, $courseFolder, $aircraftPath);
                    $result['courses_created']++;
                    $result['categories_created'] += $manifestResult['categories_created'];
                    $result['aukstructures_created'] += $manifestResult['aukstructures_created'];
                } else {
                    Course::create([
                        'title' => $courseFolder,
                        'path' => $courseFolder,
                        'aircraft_id' => $aircraft->id,
                        'hash' => Course::generateHash($aircraftPath, $courseFolder),
                        'visible' => true,
                    ]);
                    $result['courses_created']++;
                    Log::warning("imsmanifest.xml не найден для курса: {$courseFolder}");
                }

                $giftPath = $coursesPath.'/'.$courseFolder.'/GIFT';
                if (is_dir($giftPath)) {
                    $this->giftParser->setAircraftId($aircraft->id);
                    $giftFiles = array_diff(scandir($giftPath), ['..', '.']);
                    foreach ($giftFiles as $giftFile) {
                        $fullGiftPath = $giftPath.'/'.$giftFile;
                        if (is_file($fullGiftPath)) {
                            $giftResult = $this->giftParser->parse($fullGiftPath);
                            $result['questions_imported'] += $giftResult['questions_created'];
                            $result['answers_imported'] += $giftResult['answers_created'];
                            if (! empty($giftResult['errors'])) {
                                $result['errors'] = array_merge($result['errors'], $giftResult['errors']);
                            }
                        }
                    }
                }
            }

            $rootGiftPath = $coursesPath.'/GIFT';
            if (is_dir($rootGiftPath)) {
                $this->giftParser->setAircraftId($aircraft->id);
                $rootGiftFiles = array_diff(scandir($rootGiftPath), ['..', '.']);
                foreach ($rootGiftFiles as $giftFile) {
                    $fullGiftPath = $rootGiftPath.'/'.$giftFile;
                    if (is_file($fullGiftPath) && pathinfo($giftFile, PATHINFO_EXTENSION) === 'txt') {
                        preg_match('/^(\d+)\s/', $giftFile, $matches);
                        $courseNumber = $matches[1] ?? null;

                        $course = Course::where('aircraft_id', $aircraft->id)
                            ->where('path', $courseNumber)
                            ->first();

                        if ($course) {
                            $giftResult = $this->giftParser->parse($fullGiftPath);
                            $result['questions_imported'] += $giftResult['questions_created'];
                            $result['answers_imported'] += $giftResult['answers_created'];
                            if (! empty($giftResult['errors'])) {
                                $result['errors'] = array_merge($result['errors'], $giftResult['errors']);
                            }
                        }
                    }
                }
            }

            Log::info('Завершение импорта курсов', $result);

            return response()->json([
                'success' => true,
                'message' => 'Импорт завершен успешно',
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка импорта курсов: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ошибка импорта: '.$e->getMessage(),
                'result' => $result,
            ], 500);
        }
    }

    protected function parseManifest(string $contents, int $aircraftId, string $auk, string $aircraftPath): array
    {
        $result = [
            'categories_created' => 0,
            'aukstructures_created' => 0,
        ];

        $xml = simplexml_load_string($contents);
        if (! $xml) {
            Log::warning("Не удалось распарсить imsmanifest.xml для: {$auk}");

            return $result;
        }

        $resources = [];
        foreach ($xml->resources->resource as $key => $c) {
            $temp = [];
            foreach ($c->file as $cc) {
                $filename = (string) $cc->attributes()['href'];
                $ext = substr($filename, -4);
                if ($ext === 'html') {
                    $temp[] = (string) $cc->attributes()['href'];
                }
            }
            $k = (string) $c->attributes()['identifier'];
            $resources[$k] = $temp;
        }

        foreach ($xml->course->category as $key => $c) {
            Category::updateOrCreate(
                [
                    'title' => (string) $c->attributes()['name'],
                    'aircraft_id' => $aircraftId,
                ],
                [
                    'code' => (string) $c->attributes()['shortname'],
                    'description' => 'Импортировано из manifest',
                ]
            );
            $result['categories_created']++;
        }

        $curAuk = Course::updateOrCreate(
            [
                'title' => (string) $xml->organizations->organization->title,
                'aircraft_id' => $aircraftId,
            ],
            [
                'short_description' => 'Импортировано из manifest',
                'long_description' => 'Импортировано из manifest',
                'path' => $auk,
                'hash' => Course::generateHash($aircraftPath, $auk),
                'visible' => true,
            ]
        );

        $this->recurseXml(
            $xml->organizations->organization,
            0,
            0,
            $curAuk->id,
            $resources,
            $aircraftId,
            $result
        );

        return $result;
    }

    protected function recurseXml(
        $xml,
        int $auktype,
        int $parentId,
        int $courseId,
        array $resources,
        int $aircraftId,
        array &$result,
        string $attrsIdent = '',
        string $attrsCat = ''
    ): int {
        $childCount = 0;

        foreach ($xml as $key => $value) {
            if (! is_string($key) && $value && isset($value->attributes()['categories']) && isset($value->attributes()['identifierref'])) {
                $attrsIdent = (string) $value->attributes()['identifierref'];
                $attrsCat = (string) $value->attributes()['categories'];
            }
            $childCount++;

            if (is_string($key) && $key !== 'item' && $key !== '') {
                continue;
            }

            $childResults = $this->recurseXml($value, $auktype, $parentId, $courseId, $resources, $aircraftId, $result, $attrsIdent, $attrsCat);

            if ($childResults == 0 && is_string($value) && trim((string) $value) !== '') {
                $description = ['название', 'тема', 'раздел', 'модуль'];

                $el = Aukstructure::updateOrCreate(
                    [
                        'title' => (string) $value,
                        'parent_id' => (int) $parentId,
                        'course_id' => (int) $courseId,
                    ],
                    [
                        'type' => $auktype,
                        'description' => $description[$auktype] ?? 'модуль',
                        'categories' => $attrsCat,
                        'identifier' => $attrsIdent,
                    ]
                );
                $result['aukstructures_created']++;
                $parentId = $el->id;

                if ($auktype == 3 && isset($resources[$attrsIdent])) {
                    foreach ($resources[$attrsIdent] as $link) {
                        Link::updateOrCreate(
                            [
                                'link' => (string) $link,
                                'aukstructure_id' => (int) $parentId,
                            ],
                        );
                    }
                }

                if ($attrsCat) {
                    $curCats = explode(',', $attrsCat);
                    foreach ($curCats as $curCat) {
                        $curCatId = Category::where('code', trim($curCat))
                            ->where('aircraft_id', $aircraftId)
                            ->pluck('id')
                            ->first();

                        if ($curCatId) {
                            DB::table('aukstructure_category')->updateOrInsert(
                                [
                                    'category_id' => (int) $curCatId,
                                    'aukstructure_id' => (int) $parentId,
                                ],
                            );

                            DB::table('category_course')->updateOrInsert(
                                [
                                    'category_id' => (int) $curCatId,
                                    'course_id' => (int) $courseId,
                                ],
                            );
                        }
                    }
                }

                $auktype++;
            }
        }

        return $childCount;
    }

    public function clearDatabase()
    {
        try {
            DB::table('answers')->truncate();
            DB::table('questions')->truncate();
            DB::table('links')->truncate();
            DB::table('aukstructure_category')->truncate();
            DB::table('category_course')->truncate();
            DB::table('aukstructures')->truncate();
            DB::table('categories')->truncate();
            DB::table('courses')->truncate();
            DB::table('aircrafts')->truncate();

            Log::info('База данных очищена для импорта');

            return response()->json([
                'success' => true,
                'message' => 'База данных очищена',
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка очистки базы данных: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ошибка очистки: '.$e->getMessage(),
            ], 500);
        }
    }
}
