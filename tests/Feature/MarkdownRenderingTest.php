<?php

namespace Tests\Feature;

use GrahamCampbell\Markdown\Facades\Markdown;
use Tests\TestCase;

class MarkdownRenderingTest extends TestCase
{
    public function test_markdown_content_renders_to_html(): void
    {
        $html = (string) Markdown::convertToHtml('# Test title');

        $this->assertStringContainsString('<h1>Test title</h1>', $html);
    }
}
