<?php

namespace Tests\Unit;

use App\Support\GoogleDriveLink;
use PHPUnit\Framework\TestCase;

class GoogleDriveLinkTest extends TestCase
{
    public function test_it_parses_file_links(): void
    {
        $link = GoogleDriveLink::parse('https://drive.google.com/file/d/abc123XYZ/view?usp=sharing');

        $this->assertNotNull($link);
        $this->assertSame('abc123XYZ', $link->id);
        $this->assertSame('https://drive.google.com/file/d/abc123XYZ/preview', $link->previewUrl());
    }

    public function test_it_rejects_non_google_urls(): void
    {
        $this->assertNull(GoogleDriveLink::parse('https://example.com/file.pdf'));
    }
}
