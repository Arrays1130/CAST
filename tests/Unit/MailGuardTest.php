<?php

namespace Tests\Unit;

use App\Support\MailGuard;
use RuntimeException;
use Tests\TestCase;

class MailGuardTest extends TestCase
{
    public function test_allows_non_production_log_mailer(): void
    {
        $this->app['env'] = 'testing';
        config(['app.env' => 'testing', 'mail.default' => 'log']);

        MailGuard::assertDeliverable();

        $this->assertTrue(true);
    }

    public function test_blocks_production_log_mailer(): void
    {
        $this->app['env'] = 'production';
        config(['app.env' => 'production', 'mail.default' => 'log']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MAIL_MAILER=smtp');

        MailGuard::assertDeliverable();
    }
}
