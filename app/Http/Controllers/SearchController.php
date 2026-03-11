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
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'query' => 'required|string|min:2',
                'aircraft' => 'required|exists:aircraft,id',
                'path' => 'required|string'
            ]);

            $searchTerm = strtolower($validated['query']);
            $aircraft = Aircraft::findOrFail($validated['aircraft']);
            $directory = "private/{$aircraft->path}/{$validated['path']}/";

            // Check if directory exists
            if (!Storage::disk('public')->exists($directory)) {
                return response()->json(['message' => 'Directory not found'], 404);
            }

            $matches = [];
            $files = Storage::disk('public')->files($directory);

            foreach ($files as $file) {
                if (Str::endsWith($file, '.html') && !Str::endsWith($file, 'index.html')) {
                    try {
                        $contents = Storage::disk('public')->get($file);
                        $contents = strtolower($contents);

                        if (Str::contains($contents, $searchTerm)) {
                            $filename = basename($file);
                            $filename = trim($filename);

                            // Get aukstructure and link
                            $link = Link::where('link', $filename)->first();
                            $aukstructure = $link ? $link->aukstructure : Aukstructure::whereHas('links', function($query) use ($filename) {
                                $query->where('link', $filename);
                            })->first();

                            if (!$aukstructure) {
                                continue;
                            }

                            // Highlight search term
                            $highlightedHtml = $this->highlightWords($contents, $searchTerm);

                            $matches[] = [
                                'file' => $file,
                                'contents' => $highlightedHtml,
                                'title' => $aukstructure->title,
                                'itemId' => $aukstructure->id,
                                'highlightedHtml' => $highlightedHtml['highlightedNodes'],
                                'originalHtml' => $highlightedHtml['originalNodes']
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing file: ' . $file, ['error' => $e->getMessage()]);
                        continue;
                    }
                }
            }

            return response()->json($matches);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json(['message' => 'Search failed'], 500);
        }
    }

    private function highlightWords($html, $searchTerm)
    {
        try {
            // Create DOMDocument object
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            
            // Load HTML into DOMDocument object
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            // Perform search and highlight
            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//text()');
            $highlightedNodes = [];
            $originalNodes = [];

            foreach ($nodes as $node) {
                $text = $node->nodeValue;
                $pos = stripos($text, $searchTerm);
                
                if ($pos !== false) {
                    // Split text node into parts
                    $before = substr($text, 0, $pos);
                    $highlighted = substr($text, $pos, strlen($searchTerm));
                    $after = substr($text, $pos + strlen($searchTerm));

                    // Create nodes
                    $node->nodeValue = $before;

                    // Create highlighted node
                    $highlightedNode = $dom->createElement('span', htmlspecialchars($highlighted));
                    $highlightedNode->setAttribute('class', 'highlighted');
                    $highlightedNode->setAttribute('data-id', substr(md5($searchTerm), 0, 10));
                    $node->parentNode->insertBefore($highlightedNode, $node->nextSibling);

                    // Create original node
                    $originalNode = $dom->createElement('span', htmlspecialchars($text));
                    $node->parentNode->insertBefore($originalNode, $highlightedNode);

                    // Create after node if needed
                    if (!empty($after)) {
                        $afterNode = $dom->createTextNode($after);
                        $node->parentNode->insertBefore($afterNode, $highlightedNode->nextSibling);
                    }

                    $highlightedNodes[] = $highlightedNode;
                    $originalNodes[] = $originalNode;
                }
            }

            return [
                'highlightedHtml' => $dom->saveHTML(),
                'highlightedNodes' => $highlightedNodes,
                'originalNodes' => $originalNodes
            ];
        } catch (\Exception $e) {
            Log::error('Error highlighting words: ' . $e->getMessage());
            return [
                'highlightedHtml' => $html,
                'highlightedNodes' => [],
                'originalNodes' => []
            ];
        }
    }

    public function get_file_content(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'file' => 'required|string',
                'aircraft' => 'required|exists:aircraft,id',
                'path' => 'required|string'
            ]);

            $aircraft = Aircraft::findOrFail($validated['aircraft']);
            $filePath = "private/{$aircraft->path}/{$validated['path']}/{$validated['file']}";

            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $content = Storage::disk('public')->get($filePath);
            return response()->json(['content' => $content]);
        } catch (\Exception $e) {
            Log::error('Error getting file content: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get file content'], 500);
        }
    }
}