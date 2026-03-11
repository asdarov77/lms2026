<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use App\Models\Link;
use App\Models\Aukstructure;

use DOMDocument;
use DOMText;
use DOMNode;
use DOMElement;
use DOMXPath;
use DOMDocumentFragment;
use SimpleXMLElement;

use Sunra\PhpSimple\HtmlDomParser;
//include_once 'HtmlWeb.php';
//use simplehtmldom\HtmlWeb;
use Symfony\Component\DomCrawler\Crawler;
//use voku\helper\HTMLDomParser;
use Illuminate\Support\Facades\File;
use Symfony\Component\DomCrawler\DomCrawler;



class SearchController2 extends Controller
{

    public function search(Request $request)
    {
        $searchTerm = strtolower($request->input('query'));
        $aircraft = Aircraft::find($request->input('aircraft'))->path;
        $path = $request->input('path');
        $directory = "private/{$aircraft}/{$path}/";

        $matches = [];

        $files = Storage::disk('public')->files($directory);
        //var_dump($files);
        //var_dump($directory);
        foreach ($files as $file) {
            if (str_ends_with($file, '.html') && !str_ends_with($file, 'index.html')) {


                // Читаем содержимое HTML файла
                //$contents = File::get($file);
                //echo($contents);
                //return $contents;
                $contents = Storage::get($file);
                $contents = strtolower($contents);

                if (mb_stripos($contents, $searchTerm) !== false) {
                    $filename = basename($file);
                    $filename = trim($filename);
                    // Получаем id структуры из таблицы links
                    $link = Link::where('link', $filename)->first();
                    //echo($link->aukstructure_id);
                    $aukstructure = Aukstructure::find($link->aukstructure_id);
                    //echo('ok');
                    //echo($link);
                    $title = $aukstructure->title;
                    //echo($title);


                    $highlightedContents = $this->highlightWords($contents, $searchTerm); //было с вариантом words1 function              

                    echo($highlightedContents);
                    $matches[] = [
                        'file' => $file,
                        'position' => mb_stripos($contents, $searchTerm),
                        'length' => mb_strlen($searchTerm),
                        'contents' => $highlightedContents,
                        'title' => $title,
                    ];
                    //$matches[] = $file;
                }
            }
        }
        return response()->json($matches);
    }


    //------------------------------------------------------------------------------------

    function highlightWords1(string $html, string $searchTerm): string
    {
        // Remove all control characters from the HTML code
        $html = preg_replace('/[\x00-\x1F\x7F]/u', '', $html);


        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        //libxml_use_internal_errors(true); // отключение ошибок

        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        //$dom->loadHTML($html);

        //libxml_use_internal_errors(false); // включение ошибок

        $xpath = new DOMXPath($dom);
        $query = "//body//*[not(self::script)]/text()";
        $textNodes = $xpath->query($query);

        $id = 1;

        foreach ($textNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = $node->nodeValue;
                $words = preg_split('/\s+/u', $searchTerm);
                //$text = preg_replace('/<\/?(?:b|i|h1|h2|h3|h4)>/i', '', $text);
                //$text = preg_replace('/<\/?(?:b|i|h1)>/i', '', $text);
                foreach ($words as $word) {
                    $pattern = '/\b(' . preg_quote($word, '/') . ')\b/iu';
                    //  $replacement = '<b><i><h1><h2><h3>$1</h3></h2></h1></i></b>';
                    //----------------------
                    //$replacement = '<span class="highlighted" data-id="' . $id . '">$1</span>';
                    $span = $dom->createElement('span');
                    $span->setAttribute('class', 'highlighted');
                    $span->setAttribute('data-id', $id);
                    $span->nodeValue = $word;
                    $replacement = $dom->saveHTML($span);
                    //----------------------
                    $text = preg_replace($pattern, $replacement, $text, -1, $count);
                    $id += $count;
                }
                //echo($text);
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($text);
                $node->parentNode->replaceChild($fragment, $node);
            }
        }

        return $dom->saveHTML($dom->getElementsByTagName('body')[0]);
    }



    // function highlightWords(string $html, string $searchTerm): string
    // {
    //     $document = new DOMDocumentFragment();
    //     $document->appendXML($html);

    //     $xpath = new DOMXPath($document);
    //     $searchResults = $xpath->query('//body//text()[contains(., "' . $searchTerm . '")]');

    //     $count = 0;

    //     foreach ($searchResults as $result) {
    //         $span = $document->createElement('span');
    //         $span->setAttribute('data-highlight', $count++);  

    //         $resultText = $result->textContent;

    //         $pos = strpos($resultText, $searchTerm);
    //         $before = substr($resultText, 0, $pos);
    //         $after = substr($resultText, $pos + strlen($searchTerm));

    //         $span->appendChild($document->createTextNode($before));
    //         $span->appendChild($document->createTextNode($searchTerm));
    //         $span->appendChild($document->createTextNode($after));

    //         $result->parentNode->replaceChild($span, $result);
    //     }

    //     return $document->saveHTML(); 
    // }


    function highlightWords(string $html, string $searchTerm): string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);


        $crawler = new Crawler($dom);

        $nodes = $crawler->filterXPath('//body//text()[contains(., "' . $searchTerm . '")]');

        $i = 0;

        foreach ($nodes as $node) {
            $span = $dom->createElement('span');
            $span->setAttribute('data-id', $i++);
            $span->setAttribute('class', 'highlighted');

            $text = $node->nodeValue;

            $textParts = explode($searchTerm, $text);

            $fragment = '';
            foreach ($textParts as $part) {
                $fragment .= $part . $searchTerm;
            }

            $span->appendChild($dom->createTextNode($fragment));
            $node->parentNode->replaceChild($span, $node);
        }

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    //function highlightWords(string $html, string $searchTerm): string
    //{
    //     $fragment = new DOMDocumentFragment();
    //     $document = new DOMDocument();
    //     $fragment->appendXML('<html><body>' . $html . '</body></html>');

    //     $xpath = new DOMXPath($document);
    //     $searchResults = $xpath->query('//body//text()[contains(., "' . $searchTerm . '")]');

    //     $count = 0;

    //     foreach ($searchResults as $result) {
    //         $span = $document->createElement('span');
    //         $span->setAttribute('data-highlight', $count++);

    //         $resultText = $result->nodeValue;
    //         $pos = strpos($resultText, $searchTerm);
    //         $before = substr($resultText, 0, $pos);
    //         $after = substr($resultText, $pos + strlen($searchTerm));

    //         $span->appendChild($document->createTextNode($before));
    //         $highlight = $document->createElement('mark', $searchTerm);
    //         $span->appendChild($highlight);
    //         $span->appendChild($document->createTextNode($after));

    //         $result->parentNode->replaceChild($span, $result);
    //     }

    //     $body = $fragment->getElementsByTagName('body')->item(0);
    //     $html = $document->createElement('html');
    //     $html->appendChild($body);
    //     $document->appendChild($html);

    //     return $document->saveHTML();
    // }

}
