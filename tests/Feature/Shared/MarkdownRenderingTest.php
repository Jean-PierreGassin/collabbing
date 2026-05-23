<?php

namespace Tests\Feature\Shared;

use GrahamCampbell\Markdown\Facades\Markdown;
use Tests\TestCase;

class MarkdownRenderingTest extends TestCase
{
    public function testMarkdownContentRendersToHtml(): void
    {
        $html = (string) Markdown::convertToHtml('# Test title');

        $this->assertStringContainsString('<h1 id="test-title">Test title</h1>', $html);
    }

    public function testMarkdownContentStripsRawHtml(): void
    {
        $html = (string) Markdown::convertToHtml('**Safe** <script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Safe', $html);
    }

    public function testMarkdownContentBlocksUnsafeLinks(): void
    {
        $html = (string) Markdown::convertToHtml('[Bad link](javascript:alert("xss")) [Safe link](https://example.com)');

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('https://example.com', $html);
    }
}
