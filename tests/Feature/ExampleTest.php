<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_shows_the_landing_page_for_guests(): void
    {
        $this->get('/')->assertOk();
    }
}
