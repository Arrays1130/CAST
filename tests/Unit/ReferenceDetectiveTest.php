<?php

namespace Tests\Unit;

use App\Services\ReferenceDetective;
use PHPUnit\Framework\TestCase;

class ReferenceDetectiveTest extends TestCase
{
    public function test_it_flags_unused_and_missing_references(): void
    {
        $text = <<<'TXT'
Chapter 1
According to Smith (2020), the method works. Jones (2019) disagreed.

References
Smith, A. (2020). Useful methods. Journal of Things, 1(1), 1-10.
Brown, B. (2018). Unused book. Publisher.
TXT;

        $result = (new ReferenceDetective)->analyze($text);

        $this->assertSame('ok', $result['status']);
        $this->assertNotEmpty($result['unused']);
        $this->assertTrue(collect($result['unused'])->contains(fn ($line) => str_contains($line, 'Brown')));
        $this->assertNotEmpty($result['missing']);
        $this->assertTrue(collect($result['missing'])->contains(fn ($c) => str_contains($c, 'Jones')));
    }

    public function test_it_warns_when_references_heading_missing(): void
    {
        $result = (new ReferenceDetective)->analyze('Just a short body with no list.');

        $this->assertNotEmpty($result['warnings']);
    }
}
