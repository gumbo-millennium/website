<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Facades\Markdown;
use Tests\TestCase;

class MarkdownServiceTest extends TestCase
{
    public function testBasicUse(): void
    {
        $markdown = <<<'MARKDOWN'
        ## Hello World

        I am a **yellow** bandana!
        MARKDOWN;

        $result = trim(Markdown::parse($markdown));

        $this->assertEquals(<<<'HTML'
        <h2>Hello World</h2>
        <p>I am a <strong>yellow</strong> bandana!</p>
        HTML, $result);
    }

    public function testUnsafeMarkdown(): void
    {
        $markdown = <<<'markdown'
        ## Hello World

        I am a **yellow** bandana!

        <script>/* fail */</script>

        <iframe target="src"></iframe>
        markdown;

        $result = trim(Markdown::parseSafe($markdown)->toHtml());

        $this->assertEquals(<<<'HTML'
        <h2>Hello World</h2>
        <p>I am a <strong>yellow</strong> bandana!</p>
        HTML, $result);
    }

    public function testImagesAreNotSupported(): void
    {
        $markdown = <<<'markdown'
        ## Hello World

        ![Test image](https://example.com)

        Hehe
        markdown;

        $this->assertStringNotContainsString('<img', (string) Markdown::parse($markdown));
        $this->assertStringNotContainsString('<img', (string) Markdown::parseSafe($markdown)->toHtml());
    }
}
