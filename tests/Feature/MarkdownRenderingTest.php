<?php

namespace Tests\Feature;

use GrahamCampbell\Markdown\Facades\Markdown;
use Tests\TestCase;

class MarkdownRenderingTest extends TestCase
{
    public function test_markdown_content_renders_to_html(): void
    {
        $html = (string) Markdown::convertToHtml('# Test title');

        $this->assertStringContainsString('<h1 id="test-title">Test title</h1>', $html);
    }

    public function test_markdown_content_strips_raw_html(): void
    {
        $html = (string) Markdown::convertToHtml('**Safe** <script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Safe', $html);
    }

    public function test_markdown_content_blocks_unsafe_links(): void
    {
        $html = (string) Markdown::convertToHtml('[Bad link](javascript:alert("xss")) [Safe link](https://example.com)');

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('https://example.com', $html);
    }
}
