<?php

namespace Tests\Unit;

use App\GiftParser\GiftParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GiftParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_simple_gift_file(): void
    {
        $testContent = <<<'GIFT'
::Q1::What is 2+2? {
    =4
    ~3
    ~5
}

::Q2::What is the capital of France? {
    =Paris
    ~London
    ~Berlin
}
GIFT;

        Storage::fake('private');
        $testPath = storage_path('app/private/test.gift');
        file_put_contents($testPath, $testContent);

        $parser = new GiftParser;
        $result = $parser->parse($testPath);

        $this->assertGreaterThan(0, $result['questions_created']);
        $this->assertGreaterThan(0, $result['answers_created']);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'What is 2+2?',
        ]);
    }

    public function test_returns_error_for_nonexistent_file(): void
    {
        $parser = new GiftParser;
        $result = $parser->parse('/nonexistent/file.gift');

        $this->assertEquals(0, $result['questions_created']);
        $this->assertNotEmpty($result['errors']);
    }
}
