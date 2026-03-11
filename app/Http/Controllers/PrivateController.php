<?php

namespace App\Http\Controllers;

use App\Http\Services\AccessService;
use Illuminate\Support\Facades\Storage;

class PrivateController extends Controller
{
    protected AccessService $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function htmles00(string $hash)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/index.html";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            if (! $access['full_access'] && $access['category_id']) {
                $contents = $this->filterContentByCategory($contents, $access['category_id']);
            }

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles(string $hash, string $html)
    {
        if ($html === 'index.html') {
            return $this->htmles00($hash);
        }

        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $handle = Storage::readStream($path);

            return response()->stream(function () use ($handle) {
                while (! feof($handle)) {
                    $buffer = fread($handle, 8192);
                    ob_start();
                    echo $buffer;
                    ob_end_flush();
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => $headerType,
            ]);
        }

        return response('File not found', 404);
    }

    public function htmles2(string $hash, string $html, string $html2)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles3(string $hash, string $html, string $html2, string $html3)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles4(string $hash, string $html, string $html2, string $html3, string $html4)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}/{$html4}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles5(string $hash, string $html, string $html2, string $html3, string $html4, string $html5)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}/{$html4}/{$html5}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles6(string $hash, string $html, string $html2, string $html3, string $html4, string $html5, string $html6)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}/{$html4}/{$html5}/{$html6}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles7(string $hash, string $html, string $html2, string $html3, string $html4, string $html5, string $html6, string $html7)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}/{$html4}/{$html5}/{$html6}/{$html7}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    public function htmles8(string $hash, string $html, string $html2, string $html3, string $html4, string $html5, string $html6, string $html7, string $html8)
    {
        $access = $this->accessService->checkCourseAccess($hash);
        $course = $access['course'];

        $path = "private/{$course->aircraft->path}/{$course->path}/{$html}/{$html2}/{$html3}/{$html4}/{$html5}/{$html6}/{$html7}/{$html8}";
        $ext = pathinfo($path)['extension'];
        $headerType = $this->getMimeType($ext);

        if (Storage::exists($path)) {
            $contents = Storage::get($path);

            return response($contents, 200)->header('Content-Type', $headerType);
        }
        abort(404);
    }

    protected function filterContentByCategory(string $content, int $categoryId): string
    {
        return $content;
    }

    protected function getMimeType(string $ext): string
    {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'text/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'svg' => 'image/svg+xml',
            'xml' => 'application/xml',
            'html' => 'text/html',
            'htm' => 'text/html',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'json' => 'application/json',
        ];

        return $mimeTypes[$ext] ?? 'text/html';
    }
}
